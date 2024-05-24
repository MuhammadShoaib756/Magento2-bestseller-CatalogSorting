<?php

declare(strict_types=1);

namespace Shoaib\CatalogSorting\Model;

use Shoaib\CatalogSorting\Api\MethodInterface;
use Shoaib\CatalogSorting\Api\IndexMethodWrapperInterface;
use Magento\Framework\Exception\LocalizedException;

class MethodProvider
{
    /**
     * @param array $indexedMethods
     * @param array $methods
     * @throws LocalizedException
     */
    public function __construct(
        private array $indexedMethods = [],
        private array $methods = []
    ) {
        $this->initMethods($indexedMethods, $methods);
    }

    /**
     * Initialize sorting method collection
     *
     * @param IndexMethodWrapperInterface[] $indexedMethods
     * @param MethodInterface[] $methods
     * @throws LocalizedException
     */
    private function initMethods(array $indexedMethods = [], array $methods = []): void
    {
        foreach ($indexedMethods as $methodWrapper) {
            $this->indexedMethods[$methodWrapper->getSource()->getMethodCode()] = $methodWrapper;
        }
        foreach ($methods as $methodObject) {
            if (!$methodObject instanceof MethodInterface) {
                if (is_object($methodObject)) {
                    throw new LocalizedException(
                        __('Method object ' . get_class($methodObject) .
                            ' must be implemented by Shoaib\CatalogSorting\Api\MethodInterface')
                    );
                } else {
                    throw new LocalizedException(__('$methodObject is not object'));
                }
            }
            $this->methods[$methodObject->getMethodCode()] = $methodObject;
        }
    }

    /**
     * Get Sorting method bt code
     *
     * @param string $code
     * @return MethodInterface|null
     */
    public function getMethodByCode(string $code): ?MethodInterface
    {
        return $this->methods[$code] ?? null;
    }

    /**
     * Get Sorting index Method
     *
     * @return IndexMethodWrapperInterface[]
     */
    public function getIndexedMethods(): array
    {
        return $this->indexedMethods;
    }

    /**
     * Get Sorting Methods
     *
     * @return MethodInterface[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
