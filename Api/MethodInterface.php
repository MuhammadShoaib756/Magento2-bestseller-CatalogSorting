<?php

namespace Shoaib\CatalogSorting\Api;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\Store;

/**
 * Interface IndexedMethodInterface
 * @api
 */
interface MethodInterface
{
    /**
     * Apply sorting method to collection
     *
     * @param Collection $collection
     * @param string $direction
     * @return $this
     */
    public function apply(Collection $collection, string $direction): static;

    /**
     * Returns Sorting method Code for using in code
     *
     * @return string
     */
    public function getMethodCode(): string;

    /**
     * Returns Sorting method Name for using like Method label
     *
     * @return string
     */
    public function getMethodName(): string;

    /**
     * Get method label for store
     *
     * @param int|Store|null $store
     *
     * @return string
     */
    public function getMethodLabel(Store|int $store = null): string;
}
