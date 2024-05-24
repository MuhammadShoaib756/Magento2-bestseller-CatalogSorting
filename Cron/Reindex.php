<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Cron;

use Shoaib\CatalogSorting\Model\Elasticsearch\IsElasticSort;
use Shoaib\CatalogSorting\Model\Indexer\Summary;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Processor as FulltextProcessor;

class Reindex
{
    /**
     * @param Summary $summary
     * @param IsElasticSort $isElasticSort
     * @param FulltextProcessor $fulltextProcessor
     */
    public function __construct(
        private readonly Summary $summary,
        private readonly IsElasticSort $isElasticSort,
        private readonly FulltextProcessor $fulltextProcessor
    ) {
    }

    /**
     * Reindex all sorting indexable methods; trigger elasticsearch reindex if needed.
     *
     * @return void
     */
    public function execute(): void
    {
        $this->summary->reindexAll();
        if ($this->isElasticSort->execute(true)) {
            $this->fulltextProcessor->reindexAll();
        }
    }
}
