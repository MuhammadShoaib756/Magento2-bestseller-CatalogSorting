<?php

namespace Shoaib\CatalogSorting\Model;

use Shoaib\CatalogSorting\Api\MethodInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;

class SortingAdapter extends DataObject
{
    private const CACHE_TAG = 'SORTING_METHOD';

    /**
     * @var MethodInterface
     */
    private $methodModel;

    /**
     * @param MethodInterface|null $methodModel
     * @param array $data
     */
    public function __construct(
        MethodInterface $methodModel = null,
        array $data = []
    ) {
        $this->methodModel = $methodModel;
        parent::__construct($data);
        $this->prepareData();
    }

    /**
     * Set Data for call object as array
     *
     * @return void
     */
    private function prepareData(): void
    {
        if (!$this->hasData('attribute_code')) {
            $this->setData('attribute_code', $this->methodModel->getMethodCode());
        }
        if (!$this->hasData('frontend_label')) {
            $this->setData('frontend_label', $this->methodModel->getMethodName());
        }
    }

    /**
     * Get Attribute code
     *
     * @return mixed|string|null
     */
    public function getAttributeCode(): mixed
    {
        if ($this->hasData('attribute_code')) {
            return $this->_getData('attribute_code');
        }
        return $this->methodModel->getMethodCode();
    }

    /**
     * Get Attribute Frontend Label
     *
     * @return array|mixed|string|null
     */
    public function getFrontendLabel(): mixed
    {
        if ($this->hasData('frontend_label')) {
            return $this->getData('frontend_label');
        }

        return $this->methodModel->getMethodName();
    }

    /**
     * Get Attribute Frontend Label
     *
     * @return array|mixed|string|null
     */
    public function getDefaultFrontendLabel(): mixed
    {
        return $this->getFrontendLabel();
    }

    /**
     * Return frontend label for default store
     *
     * @param int|Store|null $storeId
     * @return array|mixed|string|null
     */
    public function getStoreLabel(Store|int $storeId = null): mixed
    {
        if ($this->hasData('store_label')) {
            return $this->getData('store_label');
        }

        return $this->methodModel->getMethodLabel($storeId);
    }

    /**
     * Get Identities
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getAttributeCode()];
    }

    /**
     * Set Sorting Method Model
     *
     * @param MethodInterface $methodModel
     * @return $this
     */
    public function setMethodModel(MethodInterface $methodModel): static
    {
        $this->methodModel = $methodModel;
        return $this;
    }

    /**
     * Get Sorting Method Model
     *
     * @return MethodInterface|null
     */
    public function getMethodModel(): ?MethodInterface
    {
        return $this->methodModel;
    }

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput(): string
    {
        return 'hidden';
    }

    /**
     * Get attribute name
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getName(): ?string
    {
        return $this->getAttributeCode();
    }

    /**
     * Is Use Source
     *
     * @return bool
     */
    public function usesSource(): bool
    {
        return false;
    }
}
