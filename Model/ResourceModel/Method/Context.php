<?php

namespace Shoaib\CatalogSorting\Model\ResourceModel\Method;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\ObjectManager\ContextInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Context extends DbContext implements ContextInterface
{
    /**
     * @param ResourceConnection $resource
     * @param TransactionManagerInterface $transactionManager
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param DateTime $date
     */
    public function __construct(
        ResourceConnection $resource,
        TransactionManagerInterface $transactionManager,
        ObjectRelationProcessor $objectRelationProcessor,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        private readonly DateTime $date,
    ) {
        parent::__construct($resource, $transactionManager, $objectRelationProcessor);
    }

    /**
     * Get Scope
     *
     * @return ScopeConfigInterface
     */
    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }

    /**
     * Get StoreManager
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    /**
     * Get Logger
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Get Date
     *
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }
}
