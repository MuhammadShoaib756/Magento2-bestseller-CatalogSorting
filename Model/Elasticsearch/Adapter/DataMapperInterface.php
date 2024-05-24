<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\Elasticsearch\Adapter;

interface DataMapperInterface
{
    /**
     * Prepare index data for using in search engine metadata
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(int $entityId, array $entityIndexData, int $storeId, ?array $context = []): array;
}
