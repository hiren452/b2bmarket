<?php

namespace Ced\RegistrationForm\Helper;

use Ced\RegistrationForm\Model\AttributeFactory as RegistrationAttributeFactory;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core registry.
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * url builder.
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AttributeCollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var Entity
     */
    protected $_eavEntity;

    /**
     * @var AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var RegistrationAttributeFactory
     */
    protected $registrationAttributeFactory;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param AttributeCollectionFactory $attributeCollection
     * @param Entity $eavEntity
     * @param AttributeFactory $attributeFactory
     * @param CustomerSession $customerSession
     * @param RegistrationAttributeFactory $registrationAttributeFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        Registry $registry,
        AttributeCollectionFactory $attributeCollection,
        Entity $eavEntity,
        AttributeFactory $attributeFactory,
        CustomerSession $customerSession,
        RegistrationAttributeFactory $registrationAttributeFactory,
        EavConfig $eavConfig,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_attributeFactory = $attributeFactory;
        $this->customerSession = $customerSession;
        $this->registrationAttributeFactory = $registrationAttributeFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * get All custom attribute collection.
     * @return collection
     */
    public function attributeCollectionFilter()
    {
        $arr= [];
        $model = $this->registrationAttributeFactory->create()->getCollection()->getData();

        foreach ($model as $k => $v) {
            if ($v['status'] == 1 && $v['show_in_order'] == 1) {
                $arr[]= $v['attribute_code'];
            }
        }

        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $attribute = $this->_attributeCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addVisibleFilter()
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC')
            ->addFieldToFilter('attribute_code', [
                ['in' => $arr]
            ]);

        return $attribute;
    }

    /**
     * check for custom attribute should be display in order view
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInOrder($attrId)
    {
        $isShow = 0;
        $collection = $this->registrationAttributeFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', ['eq' => $attrId])
            ->addFieldToFilter('show_in_order', ['eq' => '1']);
        if (count($collection)) {
            $isShow = 1;
        }
        return $isShow;
    }

    /**
     * check for custom attribute should be display in order email
     * @param  int  $attrId
     * @return boolean
     */
    public function isShowInEmail($attrId)
    {
        $isShow = 0;
        $collection = $this->registrationAttributeFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', ['eq' => $attrId])
            ->addFieldToFilter('show_in_email', ['eq' => '1']);
        if (count($collection)) {
            $isShow = 1;
        }
        return $isShow;
    }

    /**
     * get current customer data.
     * @param  int $customerId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer($customerId)
    {
        return $this->_attributeFactory->create()->load($customerId);
    }

    /**
     * get Image Path
     * @return string
     */
    public function getImagePath()
    {
        $customer_id = $this->customerSession->getCustomerId();
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'ced/' . $customer_id;
    }

    /**
     * get image path on order view page
     * @param  int $customer_id
     * @return string
     */
    public function getOrderImagePath($customer_id)
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'ced/' . $customer_id;
    }

    /**
     * Retrieve order model.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    public function getUsedInForms($id)
    {
        return $this->_attributeFactory->create()->load($id)->getUsedInForms();
    }
    public function getAttributeById($id)
    {
        return $this->registrationAttributeFactory->create()->load($id, 'attribute_id');
    }
    public function getAttribute($attributeCode)
    {
        $entityType = 'customer';
        return $this->eavConfig->getAttribute($entityType, $attributeCode);
    }
    public function getAttributeCollection()
    {
        return $this->registrationAttributeFactory->create()->getCollection();
    }
    /**
     * Example method to load attribute by ID
     *
     * @param int $attributeId
     * @return EavAttribute
     */
    public function getAttributesByEntityTypeId($entityTypeId)
    {
        $collection = $this->attributeCollectionFactory->create()
            ->addFieldToFilter('entity_type_id', $entityTypeId);

        return $collection;
    }
    public function getAttributeFactory()
    {
        return $this->registrationAttributeFactory->create();
    }
}
