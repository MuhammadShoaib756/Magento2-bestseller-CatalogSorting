<?php

namespace Shoaib\CatalogSorting\Plugin\Product;

use Shoaib\CatalogSorting\Model\Elasticsearch\IsElasticSort;
use Shoaib\CatalogSorting\Model\MethodProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

class Collection
{
    private const PROCESS_FLAG = 'catalogsort_process';

    /**
     * @param MethodProvider $methodProvider
     * @param IsElasticSort $isElasticSort
     * @param array $skipAttributes
     */
    public function __construct(
        private readonly MethodProvider $methodProvider,
        private readonly IsElasticSort $isElasticSort,
        private array $skipAttributes = []
    ) {
    }

    /**
     * Apply sort based on sorting attribute
     *
     * @param ProductCollection $subject
     * @param string $attribute
     * @param string $dir
     * @return array
     * @throws NoSuchEntityException
     */
    public function beforeSetOrder(ProductCollection $subject, string $attribute, string $dir = Select::SQL_DESC): array
    {
        $subject->setFlag(self::PROCESS_FLAG, true);
        $flagName = $this->getFlagName($attribute);
        if ($subject->getFlag($flagName)) {
            if ($this->isElasticSort->execute()) {
                $this->skipAttributes[] = $flagName;
            } else {
                // attribute already used in sorting; disable double sorting by renaming attribute into not existing
                $attribute .= '_ignore';
            }
        } else {
            $method = $this->methodProvider->getMethodByCode($attribute);
            if ($method) {
                $method->apply($subject, $dir);
                $attribute = $method->getAlias();
            }
            if (!$this->isElasticSort->execute()) {
                if ($attribute == 'relevance' && !$subject->getFlag($this->getFlagName('am_relevance'))) {
                    $this->addRelevanceSorting($subject, $dir);
                    $attribute = 'am_relevance';
                }
                if ($attribute == 'price') {
                    $subject->addOrder($attribute, $dir);
                    $attribute .= '_ignore';
                }
            }
            $subject->setFlag($flagName, true);
        }

        $subject->setFlag(self::PROCESS_FLAG, false);

        return [$attribute, $dir];
    }

    /**
     * Skip attribute sorting if already applied
     *
     * @param ProductCollection $subject
     * @param callable $proceed
     * @param string $attribute
     * @param string $dir
     * @return mixed
     */
    public function aroundSetOrder(
        ProductCollection $subject,
        callable $proceed,
        string $attribute,
        string $dir = Select::SQL_DESC
    ): mixed {
        $flagName = $this->getFlagName($attribute);
        if (!in_array($flagName, $this->skipAttributes)) {
            $proceed($attribute, $dir);
        }

        return $subject;
    }

    /**
     * Set Flags
     *
     * @param string $attribute
     * @return string
     */
    private function getFlagName(string $attribute): string
    {
        if ($attribute == 'price_asc' || $attribute == 'price_desc') {
            $attribute = 'price';
        }
        if (is_string($attribute)) {
            return 'sorted_by_' . $attribute;
        }

        return 'catalog_sorting';
    }

    /**
     * Apply relevance sorting
     *
     * @param ProductCollection $collection
     */
    private function addRelevanceSorting(ProductCollection $collection): void
    {
        $collection->getSelect()->columns(['am_relevance' => new \Zend_Db_Expr(
            'search_result.'. TemporaryStorage::FIELD_SCORE
        )]);
        $collection->addExpressionAttributeToSelect('am_relevance', 'am_relevance', []);

        // remove last item from columns because e.am_relevance from addExpressionAttributeToSelect not exist
        $columns = $collection->getSelect()->getPart(Select::COLUMNS);
        array_pop($columns);
        $collection->getSelect()->setPart(Select::COLUMNS, $columns);
        $collection->setFlag($this->getFlagName('am_relevance'), true);
    }
}
