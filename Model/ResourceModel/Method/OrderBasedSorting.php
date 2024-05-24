<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\ResourceModel\Method;

use Shoaib\CatalogSorting\Model\ResourceModel\Method\AbstractIndexMethod;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Db\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as OrderItemCollection;

class OrderBasedSorting extends AbstractIndexMethod
{
    public const PRODUCT_ID = 'product_id';

    public const STORE_ID = 'store_id';

    /**
     * Get Sorting Column
     *
     * @return string
     */
    public function getSortingColumnName(): string
    {
        return $this->getAdditionalData('sortingColumn');
    }

    /**
     * Get Order Name
     *
     * @return string
     */
    public function getOrderColumnName(): string
    {
        return $this->getAdditionalData('orderColumn');
    }

    /**
     * Do Reindex
     *
     * @return void
     * @throws LocalizedException
     */
    public function doReindex(): void
    {
        $select = $this->indexConnection->select();
        $select->group(['source_table.store_id', 'order_item.product_id']);

        $columns = [
            self::PRODUCT_ID => 'order_item.product_id',
            self::STORE_ID => 'source_table.store_id',
            $this->getSortingColumnName() =>
                new \Zend_Db_Expr(sprintf('SUM(order_item.%s)', $this->getOrderColumnName())),
        ];

        $select->from(
            ['source_table' => $this->getTable('sales_order')]
        )->joinInner(
            ['order_item' => $this->getTable('sales_order_item')],
            'order_item.order_id = source_table.entity_id',
            []
        )->joinLeft(
            ['order_item_parent' => $this->getTable('sales_order_item')],
            'order_item.parent_item_id = order_item_parent.item_id',
            []
        );
        $this->addFromDate($select);
        $select->reset(Select::COLUMNS)->columns($columns);
        $select->useStraightJoin();

        foreach ($this->getBatchSelectIterator('entity_id', $select) as $select) {
            $resultInfo = $this->indexConnection->fetchAll($select);
            if ($resultInfo) {
                $this->insertData($resultInfo);
            }
        }
        $this->calculateGrouped();
    }

    /**
     * Insert data in index table
     *
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    private function insertData(array $data): void
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
    }

    /**
     * Get time period for index the records according to time
     *
     * @return int
     */
    private function getPeriod(): int
    {
        return $this->configProvider->getBestsellersPeriod();
    }

    /**
     * Convert days into time format
     *
     * @param Select $select
     * @return void
     */
    private function addFromDate(Select $select): void
    {
        $period = $this->getPeriod();

        if ($period) {
            $from = $this->date->date(
                Mysql::TIMESTAMP_FORMAT,
                $this->date->timestamp() - $period * 24 * 3600
            );
            $select->where('source_table.created_at >= ?', $from);
        }
    }

    /**
     *  Count grouped products ordered qty Sum of all simple qty which grouped by parent product and store
     *
     * @return void
     * @throws LocalizedException
     */
    private function calculateGrouped(): void
    {
        $collection = $this->getAdditionalData('orderItemCollectionFactory')->create();
        $collection->addFieldToFilter('product_type', Grouped::TYPE_CODE);
        $select = $collection->getSelect();
        $select->joinLeft(
            ['source_table' => $this->getTable('sales_order')],
            'main_table.order_id = source_table.entity_id',
            []
        );
        $this->addFromDate($select);
        $result = $this->calculateItemsQty($collection);

        if (empty($result)) {
            return;
        }

        $insert = [];

        foreach ($result as $storeId => $itemCounts) {
            foreach ($itemCounts as $productId => $count) {
                $insert[] = [
                    self::PRODUCT_ID => $productId,
                    self::STORE_ID => $storeId,
                    $this->getSortingColumnName() => $count,
                ];
            }
        }

        $columns = [self::PRODUCT_ID, self::STORE_ID, $this->getSortingColumnName()];
        $this->getConnection()->insertArray($this->getMainTable(), $columns, $insert);
    }

    /**
     * Calculate item qty
     *
     * @param OrderItemCollection $collection
     * @return array
     */
    private function calculateItemsQty(OrderItemCollection $collection): array
    {
        $result = [];

        foreach ($collection->getItems() as $item) {
            $config = $item->getProductOptionByCode('super_product_config');
            $groupedId = $config[self::PRODUCT_ID];
            $storeId = $item->getStoreId();

            if (!isset($result[$storeId][$groupedId])) {
                $result[$storeId][$groupedId] = 0;
            }
            // Sum of all simple qty which grouped by parent product
            $result[$storeId][$groupedId] += $item->getQtyOrdered();
        }

        return $result;
    }

    /**
     * Get Index values from index table
     *
     * @param int $storeId
     * @param array|null $entityIds
     * @return array
     * @throws LocalizedException
     */
    public function getIndexedValues(int $storeId, ?array $entityIds = []): array
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [self::PRODUCT_ID, 'value' => $this->getSortingColumnName()]
        )->where(
            sprintf('%s = ?', self::STORE_ID),
            $storeId
        );

        if (!empty($entityIds)) {
            $select->where(
                sprintf('%s in(?)', self::PRODUCT_ID),
                $entityIds
            );
        }

        return $this->getConnection()->fetchPairs($select);
    }
}
