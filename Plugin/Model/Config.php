<?php

namespace Shoaib\CatalogSorting\Plugin\Model;

use Shoaib\CatalogSorting\Model\MethodProvider;
use Shoaib\CatalogSorting\Model\SortingAdapterFactory;
use Magento\Catalog\Model\Config as CatalogConfig;

class Config
{
    public const NEWEST_PRODUCT = 'newest_product';
    public const PRICE_LOW_TO_HIGH = 'price_low_to_high';
    public const PRICE_HIGH_TO_LOW = 'price_high_to_low';
    public const POSITION = 'position';
    public const PRICE = 'price';
    public const BESTSELLERS = 'bestsellers';

    /**
     * @param MethodProvider $methodProvider
     * @param SortingAdapterFactory $adapterFactory
     */
    public function __construct(
        private readonly MethodProvider $methodProvider,
        private readonly SortingAdapterFactory $adapterFactory,
    ) {
    }

    /**
     * Add additional sorting
     *
     * @param CatalogConfig $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeUsedForSortByArray(CatalogConfig $subject, array $options): array
    {
        $customOption[self::NEWEST_PRODUCT] = __('Newest Arrivals');
        $customOption[self::PRICE_LOW_TO_HIGH] = __('Price Low to High');
        $customOption[self::PRICE_HIGH_TO_LOW] = __('Price High to Low');
        $options = array_merge($customOption, $options);
        unset($options['created_at']);
        return $options;
    }
    /**
     * Retrieve Attributes array used for sort by
     *
     * @param CatalogConfig $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributesUsedForSortBy(CatalogConfig $subject, array $options): array
    {
        return $this->addBestSellerSort($options);
    }

    /**
     * Add bestseller sort option
     *
     * @param array $options
     * @return array
     */
    public function addBestSellerSort(array $options): array
    {
        $methods = $this->methodProvider->getMethods();
        foreach ($methods as $methodObject) {
            $code = $methodObject->getMethodCode();
            if (!isset($options[$code])) {
                $options[$code] = $this->adapterFactory->create(['methodModel' => $methodObject]);
            }
        }
        return $options;
    }
}
