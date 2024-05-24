<?php

namespace Shoaib\CatalogSorting\Model\Elasticsearch\Adapter;

use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class AdditionalFieldMapper
{
    private const ES_DATA_TYPE_STRING = 'string';
    private const ES_DATA_TYPE_TEXT = 'text';
    private const ES_DATA_TYPE_FLOAT = 'float';
    private const ES_DATA_TYPE_INT = 'integer';
    private const ES_DATA_TYPE_DATE = 'date';

    /**
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param array $fields
     */
    public function __construct(
        private readonly Session $customerSession,
        private readonly StoreManagerInterface $storeManager,
        private readonly array $fields = []
    ) {
    }

    /**
     * After GEt All Attributes Types
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result): array
    {
        foreach ($this->fields as $fieldName => $fieldType) {
            if (is_object($fieldType) && ($fieldType instanceof AdditionalFieldMapperInterface)) {
                $attributeTypes = $fieldType->getAdditionalAttributeTypes();
                // @codingStandardsIgnoreLine
                $result = array_merge($result, $attributeTypes);
                continue;
            }

            if (empty($fieldName)) {
                continue;
            }
            if ($this->isValidFieldType($fieldType)) {
                $result[$fieldName] = ['type' => $fieldType];
            }
        }

        return $result;
    }

    /**
     * Shoaib Elastic entity builder plugin
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildEntityFields($subject, array $result): array
    {
        return $this->afterGetAllAttributesTypes($subject, $result);
    }

    /**
     * Get Field name if matches for addtional attribute mapping in elasticsearch
     *
     * @param mixed $subject
     * @param callable $proceed
     * @param string $attributeCode
     * @param array $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return string
     */
    public function aroundGetFieldName($subject, callable $proceed, $attributeCode, $context = []): string
    {
        if (isset($this->fields[$attributeCode]) && is_object($this->fields[$attributeCode])) {
            $filedMapper = $this->fields[$attributeCode];
            if ($filedMapper instanceof AdditionalFieldMapperInterface) {
                return $filedMapper->getFiledName($context);
            }
        }
        return $proceed($attributeCode, $context);
    }

    /**
     * Map additional fields
     *
     * @param mixed $subject
     * @param callable $proceed
     * @param string $fieldName
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundMapFieldName($subject, callable $proceed, $fieldName): mixed
    {
        if (isset($this->fields[$fieldName]) && is_object($this->fields[$fieldName])) {
            $filedMapper = $this->fields[$fieldName];
            if ($filedMapper instanceof AdditionalFieldMapperInterface) {
                $context = [
                    'customerGroupId' => $this->customerSession->getCustomerGroupId(),
                    'websiteId'       => $this->storeManager->getWebsite()->getId()
                ];
                return $filedMapper->getFiledName($context);
            }
        }
        return $proceed($fieldName);
    }

    /**
     * Check if the field type is Valid
     *
     * @param string $fieldType
     * @return bool
     */
    private function isValidFieldType(string $fieldType): bool
    {
        switch ($fieldType) {
            case self::ES_DATA_TYPE_STRING:
            case self::ES_DATA_TYPE_DATE:
            case self::ES_DATA_TYPE_INT:
            case self::ES_DATA_TYPE_FLOAT:
                break;
            default:
                $fieldType = false;
                break;
        }
        return $fieldType;
    }
}
