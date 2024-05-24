<?php

namespace Shoaib\CatalogSorting\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;

class ConfigProviderAbstract
{

    /**
     * @var string
     */
    protected string $pathPrefix = '/';

    /**
     * ConfigProviderAbstract constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
    ) {
        if ($this->pathPrefix === '/') {
            throw new \LogicException('$pathPrefix should be declared');
        }
    }

    /**
     * Clear local storage
     *
     * @return void
     */
    public function clean(): void
    {
        $this->data = [];
    }

    /**
     * Get Config Value
     *
     * @param string $path
     * @param null|int|string $storeId
     * @param string $scope
     * @return mixed
     */
    protected function getValue(
        string $path,
        null|int|string $storeId = null,
        string $scope = ScopeInterface::SCOPE_STORE
    ): mixed {
        if ($storeId === null && $scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return $this->scopeConfig->getValue($this->pathPrefix . $path, $scope, $storeId);
        }

        if ($storeId instanceof AppScopeInterface) {
            $storeId = $storeId->getId();
        }
        $scopeKey = $storeId . $scope;
        if (!isset($this->data[$path]) || !\array_key_exists($scopeKey, $this->data[$path])) {
            $this->data[$path][$scopeKey] = $this->scopeConfig->getValue($this->pathPrefix . $path, $scope, $storeId);
        }

        return $this->data[$path][$scopeKey];
    }
}
