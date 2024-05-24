<?php

namespace Shoaib\CatalogSorting\Plugin\Model\Category\Attribute\Source;

use Magento\Catalog\Model\Category\Attribute\Source\Sortby as CatalogSortby;
use Shoaib\CatalogSorting\Plugin\Model\Config;

class Sortby
{

    /**
     * Added the custom sortyby filters in admin panel category page
     *
     * @param CatalogSortby $subject
     * @param array $options
     * @return array
     */
    public function afterGetAllOptions(CatalogSortby $subject, array $options): array
    {
        $options[] = ['label' => __('Newest Arrivals'), 'value' => Config::NEWEST_PRODUCT];
        $options[] = ['label' => __('Price Low to High'), 'value' => Config::PRICE_LOW_TO_HIGH];
        $options[] = ['label' => __('Price High to Low'), 'value' => Config::PRICE_HIGH_TO_LOW];
        return $options;
    }
}
