<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model\Elasticsearch;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;

class IsElasticSort
{

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManager $storeManager
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManager $storeManager
    ) {
    }

    /**
     * Check if elasticsearch is being used or not
     *
     * @param bool $skipStoreCheck
     * @return bool
     * @throws NoSuchEntityException
     */
    public function execute(bool $skipStoreCheck = false): bool
    {
        return strpos($this->scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH), 'elast') !== false
            && ($skipStoreCheck || $this->storeManager->getStore()->getId());
    }
}
