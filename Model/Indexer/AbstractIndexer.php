<?php

namespace Shoaib\CatalogSorting\Model\Indexer;

use Shoaib\CatalogSorting\Api\IndexedMethodInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;

class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{

    /**
     * @param IndexedMethodInterface $indexBuilder
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     * @param Registry $registry
     */
    public function __construct(
        private readonly IndexedMethodInterface $indexBuilder,
        private readonly CacheContext $cacheContext,
        private readonly ManagerInterface $eventManager,
        private readonly Registry $registry,
    ) {
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @throws LocalizedException
     */
    public function execute($ids): void
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull(): void
    {
        if (!$this->registry->registry('reindex_' . $this->indexBuilder->getMethodCode())) {
            $this->indexBuilder->reindex();
            $this->cacheContext->registerTags(
                ['sorted_by_' . $this->indexBuilder->getMethodCode()]
            );
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
            $this->registry->register('reindex_' . $this->indexBuilder->getMethodCode(), true);
        }
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @throws LocalizedException
     */
    public function executeList(array $ids):void
    {
        if (!$ids) {
            throw new LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * TODO: implement partial reindex
     *
     * @param int[] $ids
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    protected function doExecuteList(array $ids) : void
    {
    }

    /**
     * TODO: implement partial reindex
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    private function doExecuteRow(int $id): void
    {
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     * @throws LocalizedException
     */
    public function executeRow($id): void
    {
        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->doExecuteRow($id);
    }
}
