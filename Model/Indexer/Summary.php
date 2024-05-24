<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\Indexer;

use Shoaib\CatalogSorting\Model\Indexer\Bestsellers\BestsellersProcessor;
use Magento\Framework\Indexer\IndexerRegistry;

class Summary
{

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param array $indexerIds
     */
    public function __construct(
        private readonly IndexerRegistry $indexerRegistry,
        private array $indexerIds = [BestsellersProcessor::INDEXER_ID]
    ) {
    }

    /**
     * Reindex All
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll(): void
    {
        foreach ($this->indexerIds as $indexerId) {
            $indexer = $this->indexerRegistry->get($indexerId);
            if ($indexer) {
                $indexer->reindexAll();
            }
        }
    }
}
