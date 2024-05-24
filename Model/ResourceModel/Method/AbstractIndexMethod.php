<?php

namespace Shoaib\CatalogSorting\Model\ResourceModel\Method;

use Shoaib\CatalogSorting\Api\IndexedMethodInterface;
use Shoaib\CatalogSorting\Model\ResourceModel\Method\AbstractMethod;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Query\BatchIteratorInterface;
use Magento\Framework\DB\Query\Generator as BatchQueryGenerator;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotDeleteException;

abstract class AbstractIndexMethod extends AbstractMethod implements IndexedMethodInterface
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        // product_id can be not unique
        $this->_init($this->getIndexTableName(), 'product_id');
    }

    /**
     * Do Reindex
     *
     * @return void
     */
    abstract public function doReindex(): void;

    /**
     * Truncate the index table before reindex
     *
     * @return $this
     * @throws CouldNotDeleteException
     */
    public function beforeReindex(): static
    {
        try {
            $this->getConnection()->truncateTable($this->getMainTable());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Error while clear index catalog sorting method: '), $e);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function reindex(): void
    {
        if ($this->getConnection()->getTransactionLevel() == 0) {
            $this->beforeReindex();

            try {
                if ($this->indexConnection) {
                    $this->doReindex();
                }
            } catch (\Exception $e) {
                $this->logger->critical(
                    $e,
                    ['method_code' => $this->getMethodCode()]
                );
                throw $e;
            }

            $this->afterReindex();
        }
    }

    /**
     * After Reindex
     *
     * @return $this
     */
    public function afterReindex(): static
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMainTable(): string
    {
        return $this->getTable($this->getIndexTableName());
    }

    /**
     * Get Table name
     *
     * @return string
     */
    public function getIndexTableName(): string
    {
        return 'catalog_sorting_' . $this->getMethodCode();
    }

    /**
     * @inheritdoc
     */
    public function getSortingColumnName(): string
    {
        return $this->getMethodCode();
    }

    /**
     * Get Method code
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->getMethodCode();
    }

    /**
     * @inheritdoc
     */
    public function apply(Collection $collection, $direction): static
    {
        try {
            $collection->joinField(
                $this->getAlias(),        // alias
                $this->getIndexTableName(),    // table
                $this->getSortingColumnName(), // field
                'product_id = entity_id',      // bind
                ['store_id' => $this->storeManager->getStore()->getId()], // conditions
                'left'                         // join type
            );
        } catch (LocalizedException $e) {
            // A joined field with this alias is already declared.
            $this->logger->warning(
                'Failed on join table for catalog sorting method: ' . $e->getMessage(),
                ['method_code' => $this->getMethodCode()]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e, ['method_code' => $this->getMethodCode()]);
        }

        return $this;
    }

    /**
     * Get Data batch by batch
     *
     * @param string $rangeField
     * @param Select $select
     * @return BatchIteratorInterface
     * @throws LocalizedException
     */
    protected function getBatchSelectIterator(string $rangeField, Select $select): BatchIteratorInterface
    {
        return $this->getBatchQueryGenerator()->generate(
            $rangeField,
            $select,
            $this->getBatchSize(),
            BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );
    }

    /**
     * Get Batch size
     *
     * @return int
     */
    private function getBatchSize(): int
    {
        return (int) ($this->getAdditionalData('batchSize') ?? 1000);
    }

    /**
     * Get Batch Query Generator
     *
     * @return BatchQueryGenerator
     */
    protected function getBatchQueryGenerator(): BatchQueryGenerator
    {
        return $this->getAdditionalData('batchQueryGenerator');
    }
}
