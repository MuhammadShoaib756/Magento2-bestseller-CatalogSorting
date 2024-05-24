<?php

namespace Shoaib\CatalogSorting\Model\ResourceModel\Method;

use Shoaib\CatalogSorting\Api\MethodInterface;
use Shoaib\CatalogSorting\Model\ConfigProvider;
use Shoaib\CatalogSorting\Model\Elasticsearch\IsElasticSort;
use Shoaib\CatalogSorting\Model\ResourceModel\Method\Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractMethod extends AbstractDb implements MethodInterface
{
    /**
     * @var bool
     */
    public const ENABLED = true;

    /**
     * @param Context $context
     * @param Escaper $escaper
     * @param ConfigProvider $configProvider
     * @param IsElasticSort $isElasticSort
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param DateTime $date
     * @param string|null $connectionName
     * @param string $methodCode
     * @param string $methodName
     * @param AbstractDb|null $indexResource
     * @param string|null $indexConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Escaper $escaper,
        protected ConfigProvider$configProvider,
        protected IsElasticSort $isElasticSort,
        protected ScopeConfigInterface  $scopeConfig,
        protected StoreManagerInterface $storeManager,
        protected LoggerInterface $logger,
        protected DateTime $date,
        string $connectionName = null,
        protected string $methodCode = '',
        protected string $methodName = '',
        protected ?AbstractDb $indexResource = null,
        protected ?AdapterInterface $indexConnection = null,
        private readonly array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        $this->logger = $context->getLogger();
        $this->date = $context->getDate();
        if ($indexResource) {
            $this->indexConnection = $indexResource->getConnection();
        }

        parent::__construct($context, $connectionName);
    }

    //@codingStandardsIgnoreStart
    protected function _construct()
    {
        // dummy
    }
    //@codingStandardsIgnoreEnd

    /**
     * @inheritdoc
     */
    abstract public function apply(Collection $collection, $direction): static;

    /**
     * Check if Method already applied
     *
     * @param Collection $collection
     * @return bool
     */
    protected function isMethodAlreadyApplied(Collection $collection): bool
    {
        return (bool) $collection->getFlag($this->getFlagName());
    }

    /**
     * Set method Marked as applied
     *
     * @param Collection $collection
     */
    protected function markApplied(Collection $collection): void
    {
        $collection->setFlag($this->getFlagName(), true);
    }

    /**
     * Get Flag Name
     *
     * @return string
     */
    protected function getFlagName(): string
    {
        return  'sorted_by_' . $this->getMethodCode();
    }

    /**
     * Get Index values from index table
     *
     * @param int $storeId
     * @param array|null $entityIds
     * @return array
     */
    abstract public function getIndexedValues(int $storeId, ?array $entityIds = []): array;

    /**
     * Get Method code
     *
     * @return string
     */
    public function getMethodCode(): string
    {
        if (empty($this->methodCode)) {
            $this->logger->warning('Undefined Catalog sorting method code, add method code to di.xml');
        }
        return $this->methodCode;
    }

    /**
     * Get Method name
     *
     * @return string
     */
    public function getMethodName(): string
    {
        if (empty($this->methodCode)) {
            $this->logger->warning('Undefined Catalog sorting method code, add method code to di.xml');
        }
        return $this->methodName;
    }

    /**
     * @inheritdoc
     */
    public function getMethodLabel(Store|int $store = null): string
    {
        $label = __($this->getMethodName());
        return $this->escaper->escapeHtml($label);
    }

    /**
     * Get Additional Data set in DI
     *
     * @param string $key
     * @return mixed
     */
    protected function getAdditionalData(string $key): mixed
    {
        $result = null;
        if (isset($this->data[$key])) {
            $result = $this->data[$key];
        }

        return $result;
    }
}
