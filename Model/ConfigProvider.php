<?php

namespace Shoaib\CatalogSorting\Model;

use Shoaib\CatalogSorting\Model\ConfigProviderAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    private const BESTSELLER_PERIOD_PATH = 'general/period';

    /**
     * @var string
     */
    protected string $pathPrefix = 'bestsellers/';

    /**
     * Get time period for index the records according to time
     *
     * @return int
     */
    public function getBestsellersPeriod(): int
    {
        return (int)$this->getValue(self::BESTSELLER_PERIOD_PATH);
    }
}
