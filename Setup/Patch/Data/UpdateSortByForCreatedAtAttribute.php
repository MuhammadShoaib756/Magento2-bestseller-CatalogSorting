<?php

namespace Shoaib\CatalogSorting\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class UpdateSortByForCreatedAtAttribute implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private readonly EavSetupFactory $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private readonly ModuleDataSetupInterface $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        // Specify the dependencies for this data patch if any
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        // Specify aliases for this data patch if any
        return [];
    }

    /**
     * Apply the patch
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        // Update the used_for_sort_by value for created_at attribute in eav_attribute table
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'created_at');
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(Attribute::ENTITY),
            ['used_for_sort_by' => 1],
            ['attribute_id = ?' => $attributeId]
        );
        $attributeId = $eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'price');
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable(Attribute::ENTITY),
            ['used_for_sort_by' => 0],
            ['attribute_id = ?' => $attributeId]
        );
        $this->moduleDataSetup->endSetup();
    }
}
