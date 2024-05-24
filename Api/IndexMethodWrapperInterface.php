<?php

namespace Shoaib\CatalogSorting\Api;

use Magento\Framework\Indexer\ActionInterface;

interface IndexMethodWrapperInterface
{
    /**
     * Get Source
     *
     * @return IndexedMethodInterface
     */
    public function getSource(): IndexedMethodInterface;

    /**
     * Get Indexer
     *
     * @return ActionInterface
     */
    public function getIndexer(): ActionInterface;
}
