<?php

namespace Shoaib\CatalogSorting\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

abstract class AbstractSortingProcessor extends AbstractProcessor
{
    /**
     * Invalidate the indexer
     *
     * @return void
     */
    public function markIndexerAsInvalid(): void
    {
        if ($this->isIndexerScheduled()) {
            parent::markIndexerAsInvalid();
        }
    }
}
