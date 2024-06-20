<?php

namespace Matrix\NoncatalogueRfq\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddRfqAttributes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerEntity = $eavSetup->getEntityTypeId(Customer::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($customerEntity);
        $attributeGroupId = $this->attributeSetFactory->create()->getDefaultGroupId($attributeSetId);

        // Buyer Visibility
        $eavSetup->addAttribute(Customer::ENTITY, 'rfqbuyer_isvisible', [
            'type' => 'int',
            'label' => 'Visible to Seller For Non-Catalog RFQ',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'user_defined' => false,
            'position' => 1000,
            'system' => 0,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'default' => 0,
        ]);

        $attribute = $eavSetup->getAttribute(Customer::ENTITY, 'rfqbuyer_isvisible');
        $eavSetup->addAttributeToSet(
            Customer::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            $attribute['attribute_id']
        );

        // Seller Visibility
        $eavSetup->addAttribute(Customer::ENTITY, 'rfqseller_isvisible', [
            'type' => 'int',
            'label' => 'Visible to Buyer For Non-Catalog RFQ',
            'input' => 'boolean',
            'required' => false,
            'visible' => true,
            'user_defined' => false,
            'position' => 1000,
            'system' => 0,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
            'default' => 0,
        ]);

        $attribute = $eavSetup->getAttribute(Customer::ENTITY, 'rfqseller_isvisible');
        $eavSetup->addAttributeToSet(
            Customer::ENTITY,
            $attributeSetId,
            $attributeGroupId,
            $attribute['attribute_id']
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
