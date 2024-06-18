<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddDefaultPhoneNumberAttribute implements DataPatchInterface
{
    public const ATTRIBUTE_CODE = 'default_phone_number';
    public const FRONTEND_CLASS = 'wk-otp-telephone';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory     $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::ATTRIBUTE_CODE,
            [
            'group' => 'General',
            'type' => 'varchar',
            'label' => 'Default Phone Number',
            'input' => 'text',
            'backend' => \Webkul\Otp\Model\Attribute\Backend\DefaultPhoneNumber::class,
            'required' => false,
            'sort_order' => 50,
            'frontend_class' => self::FRONTEND_CLASS,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'visible' => true,
            'is_html_allowed_on_front' => true,
            'visible_on_front' => true,
            'used_in_forms' => [
                'adminhtml_customer',
                'customer_acccount_create',
                'customer_account_edit',
            ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
