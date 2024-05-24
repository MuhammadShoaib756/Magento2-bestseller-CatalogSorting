<?php

namespace Shoaib\CatalogSorting\Plugin\Elasticsearch\Model\Adapter\BatchDataMapper;

use Shoaib\CatalogSorting\Model\Elasticsearch\Adapter\DataMapperInterface;
use Shoaib\CatalogSorting\Model\Elasticsearch\Adapter\IndexedDataMapper;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;

class AdditionalProductDataMapper
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers;

    /**
     * @param array $dataMappers
     */
    public function __construct(
        array $dataMappers = []
    ) {
        $this->dataMappers = $dataMappers;
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ProductDataMapper $subject
     * @param callable $result
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function afterMap(
        $subject,
        array $result,
        array $documentData,
        int $storeId,
        array $context = []
    ): array {
        $productIds = array_keys($result);
        foreach ($result as $productId => $document) {
            $context['document'] = $document;
            foreach ($this->dataMappers as $mapper) {
                if ($mapper instanceof DataMapperInterface) {
                    if ($mapper instanceof IndexedDataMapper) {
                        $mapper->loadEntities($storeId, $productIds);
                    }
                    //@codingStandardsIgnoreLine
                    $document = array_merge($document, $mapper->map($productId, $document, $storeId, $context));
                }
            }
            $result[$productId] = $document;
        }
        $this->clearData($storeId);

        return $result;
    }

    /**
     * Clear Mapper value
     *
     * @param int $storeId
     * @return void
     */
    private function clearData(int $storeId): void
    {
        foreach ($this->dataMappers as $mapper) {
            if ($mapper instanceof IndexedDataMapper) {
                $mapper->clearValues();
            }
        }
    }
}
