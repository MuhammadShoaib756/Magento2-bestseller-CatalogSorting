<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\Elasticsearch\Adapter;

use Shoaib\CatalogSorting\Model\ResourceModel\Method\AbstractMethod;

abstract class IndexedDataMapper implements DataMapperInterface
{
    private const DEFAULT_VALUE = 0;

    /**
     * @param AbstractMethod $resourceMethod
     * @param array|null $values
     */
    public function __construct(
        protected readonly AbstractMethod $resourceMethod,
        protected array|null $values = null
    ) {
    }

    /**
     * Get Indexer table
     *
     * @return string
     */
    abstract public function getIndexerCode(): string;

    /**
     * Get Value from indexer table
     *
     * @param int $storeId
     * @param array|null $entityIds
     * @return array
     */
    protected function forceLoad(int $storeId, ?array $entityIds = []): array
    {
        return $this->resourceMethod->getIndexedValues($storeId, $entityIds);
    }

    /**
     * Map values
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array|null $context
     * @return array|int[]
     */
    public function map(int $entityId, array $entityIndexData, int $storeId, ?array $context = []): array
    {
        $value = $this->values[$entityId] ?? self::DEFAULT_VALUE;

        return [static::FIELD_NAME => $value];
    }

    /**
     * Load specific entries
     *
     * @param int $storeId
     * @param array $entityIds
     * @return void
     */
    public function loadEntities(int $storeId, array $entityIds): void
    {
        if ($this->values === null) {
            $this->values = $this->forceLoad($storeId, $entityIds);
        }
    }

    /**
     * Clear values
     *
     * @return void
     */
    public function clearValues(): void
    {
        $this->values = null;
    }
}
