<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_RegistrationForm
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Ced\RegistrationForm\Block;

use Ced\RegistrationForm\Model\AttributeFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as CustomerAttributeCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity as EavEntity;
use Magento\Eav\Model\Entity\AttributeFactory as EavAttributeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class AdditionalInfo extends Template
{
    protected $attributeCollection;
    protected $eavEntity;
    protected $customerFactory;
    protected $session;
    protected $attributeFactory;
    protected $eavAttributeFactory;

    /**
     * AdditionalInfo constructor.
     *
     * @param Context $context
     * @param CustomerAttributeCollectionFactory $attributeCollection
     * @param EavEntity $eavEntity
     * @param CustomerFactory $customerFactory
     * @param AttributeFactory $attributeFactory
     * @param EavAttributeFactory $eavAttributeFactory
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerAttributeCollectionFactory $attributeCollection,
        EavEntity $eavEntity,
        CustomerFactory $customerFactory,
        AttributeFactory $attributeFactory,
        EavAttributeFactory $eavAttributeFactory,
        Session $session,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeCollection = $attributeCollection;
        $this->eavEntity = $eavEntity;
        $this->customerFactory = $customerFactory;
        $this->attributeFactory = $attributeFactory;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->session = $session;
    }

    public function attrCollection()
    {
        $arr = [];
        $attributeModelCollection = $this->attributeFactory->create()->getCollection()->getData();

        foreach ($attributeModelCollection as $attribute) {
            if ($attribute['show_in_registration_form'] == 1 && $attribute['status'] == 1) {
                $arr[] = $attribute['attribute_code'];
            }
        }

        $eavAttributeCollection = $this->eavAttributeFactory->create()->getCollection()
            ->addFieldToFilter('entity_type_id', 1)
            ->addFieldToFilter('attribute_code', ['in' => $arr]);

        return $eavAttributeCollection;
    }

    public function getCustomer()
    {
        $customerId = $this->session->getCustomer()->getId();
        return $this->customerFactory->create()->load($customerId);
    }

    public function getForms($id)
    {
        return $this->attributeFactory->create()->load($id)->getUsedInForms();
    }

    public function cmp($a, $b)
    {
        return $a['status'] <=> $b['status'];
    }
}
