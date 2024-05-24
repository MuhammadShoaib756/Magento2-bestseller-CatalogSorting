<?php

namespace Shoaib\CatalogSorting\Model\Indexer;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Indexer\AbstractProcessor;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class ConfigInvalidateAbstract extends Value
{
    /**
     * @var AbstractProcessor
     */
    private $indexProcessor;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractProcessor $indexProcessor
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractProcessor $indexProcessor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->indexProcessor = $indexProcessor;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(): ConfigInvalidateAbstract|Value
    {
        $this->_getResource()->addCommitCallback([$this, 'processValue']);
        return parent::afterSave();
    }

    /**
     * Invalidate index if Status or Period changed
     *
     * @return void
     */
    public function processValue(): void
    {
        if ($this->getValue() != $this->getOldValue()) {
            $this->indexProcessor->markIndexerAsInvalid();
        }
    }
}
