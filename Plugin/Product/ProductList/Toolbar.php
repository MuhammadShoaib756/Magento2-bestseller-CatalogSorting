<?php

namespace Shoaib\CatalogSorting\Plugin\Product\ProductList;

use Magento\Framework\Data\Collection;
use Magento\Catalog\Block\Product\ProductList\Toolbar as CategoryToolbar;
use Shoaib\CatalogSorting\Plugin\Model\Config;

class Toolbar extends CategoryToolbar
{

    /**
     * Set collection to pager
     *
     * @param Collection $collection
     * @return $this|Toolbar
     */
    public function setCollection($collection): Toolbar|static
    {
        $this->_collection = $collection;

        $this->_collection->setCurPage($this->getCurrentPage());

        // we need to set pagination only if passed value integer and more that 0
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            if ($this->getCurrentOrder() === Config::NEWEST_PRODUCT) {
                $this->_collection->setOrder('created_at', 'desc');
            } elseif ($this->getCurrentOrder() === Config::PRICE_LOW_TO_HIGH) {
                $this->_collection->setOrder('price', 'asc');
            } elseif ($this->getCurrentOrder() === Config::PRICE_HIGH_TO_LOW) {
                $this->_collection->setOrder('price', 'desc');
            } elseif (($this->getCurrentOrder()) == Config::POSITION) {
                $this->_collection->addAttributeToSort(
                    $this->getCurrentOrder(),
                    $this->getCurrentDirection()
                );
            } elseif ($this->getCurrentOrder() === Config::BESTSELLERS) {
                $this->_collection->setOrder($this->getCurrentOrder(), 'desc');
            } else {
                $this->_collection->setOrder($this->getCurrentOrder(), $this->getCurrentDirection());
            }
        }
        return $this;
    }

    /**
     * Set default Order field
     *
     * @param string $field
     * @return $this
     */
    public function setDefaultOrder($field): static
    {
        $this->loadAvailableOrders();
        if (isset($this->_availableOrder[$field])) {
            if ($this->getRequest()->getModuleName() === 'catalogsearch') {
                $this->_orderField = 'relevance';
            } else {
                $this->_orderField = 'bestsellers';
            }
        }
        return $this;
    }

    /**
     * Load Available Orders
     *
     * @return $this
     */
    private function loadAvailableOrders(): static
    {
        if ($this->_availableOrder === null) {
            $this->_availableOrder = $this->_catalogConfig->getAttributeUsedForSortByArray();
        }
        return $this;
    }
}
