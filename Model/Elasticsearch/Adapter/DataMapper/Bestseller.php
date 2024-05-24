<?php

namespace Shoaib\CatalogSorting\Model\Elasticsearch\Adapter\DataMapper;

use Shoaib\CatalogSorting\Model\Elasticsearch\Adapter\IndexedDataMapper;

class Bestseller extends IndexedDataMapper
{
    public const FIELD_NAME = 'bestsellers';

    /**
     * @inheritdoc
     */
    public function getIndexerCode(): string
    {
        return 'catalog_sorting_bestseller';
    }
}
