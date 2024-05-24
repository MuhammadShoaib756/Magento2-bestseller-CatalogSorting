<?php

namespace Shoaib\CatalogSorting\Model;

use Shoaib\CatalogSorting\Api\IndexMethodWrapperInterface;
use Shoaib\CatalogSorting\Api\IndexedMethodInterface;
use Shoaib\CatalogSorting\Model\Indexer\AbstractIndexer;
use Magento\Framework\Indexer\ActionInterface;

class IndexMethodWrapper implements IndexMethodWrapperInterface
{

    /**
     * @param IndexedMethodInterface $source
     * @param AbstractIndexer $indexer
     */
    public function __construct(
        private readonly IndexedMethodInterface $source,
        private readonly AbstractIndexer $indexer
    ) {
    }

    /**
     * Get Source
     *
     * @return IndexedMethodInterface
     */
    public function getSource(): IndexedMethodInterface
    {
        return $this->source;
    }

    /**
     * Get Indexer
     *
     * @return AbstractIndexer
     */
    public function getIndexer(): ActionInterface
    {
        return $this->indexer;
    }
}
