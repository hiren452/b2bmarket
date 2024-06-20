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
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Matrix\RegistrationForm\Block;

use Ced\RegistrationForm\Model\Attribute\ResourceModel\Attribute\Collection;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;

class Additional extends \Ced\RegistrationForm\Block\Additional
{
    protected $_attributeCollection;

    protected $_eavEntity;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;
    protected $_attributeFactory;
    private $_currentCustomer;
    protected $_storeManager;

    /**
     * Additional constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param CustomerFactory $customer
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Eav\Model\Entity $eavEntity,
        CustomerFactory $customer,
        \Magento\Customer\Model\AttributeFactory $attributeFactory,
        Session $session,
        Collection $cedCollection,
        AttributeCollectionFactory $attributeCollectionFactory,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $attributeCollection,
            $eavEntity,
            $customer,
            $attributeFactory,
            $session,
            $data
        );
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_attributeFactory = $attributeFactory;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->cedCollection = $cedCollection;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
    }

    /* Final attribute collection */
    public function attrCollection()
    {
        $arr= [];
        $model = $this->cedCollection->getCollection()->getData();
        foreach ($model as $k => $v) {
            if ($v['show_in_customer_account'] == 1 && $v['status'] == 1) {
                $arr[]= $v['attribute_code'];
            }
        }
        $attribute = $this->_attributeCollectionFactory->getCollection()->addFieldToFilter('entity_type_id', 1);
        $attribute->addFieldToFilter('attribute_code', [
            ['in' => $arr]
        ]);

        $collection = ($attribute->getData());
        $attributeCollection =[];
        $viewModel = $this->getData('additionalInfoViewModel');
        foreach ($collection as $k => $v) {
            $models =  $viewModel->getAttributeVaue($v['attribute_code'], 'attribute_code');
            $v['fieldAttribute'] = '';
            if ($v['attribute_code'] == 'buyer_registration_document_upload') {
                $v['fieldAttribute'] = "accept='.txt, .pdf' data-validate='{multistepregvalidationrule:true}'";
                $v['file_note'] = "Registration document upload types are .txt and .pdf";
            }
            $v['sortorder'] = $models['sortorder'];
            $v['dependable_attribute'] = $models['dependable_attribute'];
            $v['is_dependent'] = $models['is_dependent'];
            $v['is_time'] = $models['is_time'];
            $attributeCollection[]  = $v;
        }
        usort($attributeCollection, [$this,'cmp']);
        return $attributeCollection;
        return $attribute;
    }

    public function getDependableAttribute()
    {
        $attributeCollection = $this->attrCollection();
        $dependable = [];
        foreach ($attributeCollection as $value) {
            if ($value['dependable_attribute'] !="") {
                $dependable[] = $value['dependable_attribute'];
            }
        }
        return $dependable;
    }

    public function getCustomer()
    {
        $customerId = $this->_session->getCustomer()->getId();
        if ($this->_currentCustomer == null) {
            $this->_currentCustomer = $this->_customer->create()->load($customerId);
        }
        return $this->_currentCustomer;
    }

    public function getCustomerInputData($attribute)
    {
        $inputValue = '';
        $_customerData = $this->getCustomer()->getData();
        foreach ($_customerData as $key => $value) {
            if ($attribute['attribute_code'] == $key) {
                if ($attribute['frontend_input'] == 'date') {
                    $inputValue = $value;
                } elseif ($attribute['frontend_input'] == 'boolean') {
                    $inputValue = 'checked';
                } elseif ($attribute['frontend_input'] == 'select') {
                    $inputValue = $value;
                } elseif ($attribute['frontend_input'] == 'multiselect') {
                    $inputValue = $value;
                } elseif ($attribute['frontend_input'] == 'image') {
                    $inputValue = "<img class='images' width='75' height='75' alt='image' src='" . $this->getMediaUrl() . $value . "'/>";
                } elseif ($attribute['frontend_input'] == 'file') {
                    $inputValue = "<a class='images' alt='file' href='" . $this->getMediaUrl() . 'customer' . $value . "'>" . $value . "</a>";
                } else {
                    $inputValue = $value;
                }
            }
        }
        return $inputValue;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    public function getForms($id)
    {
        $attributeModel = $this->_attributeFactory->create();
        return $attributeModel->load($id)->getUsedInForms();
    }

    public function cmp($a, $b)
    {
        if ($a['sortorder'] == $b['sortorder']) {
            return 0;
        }
        return ($a['sortorder'] < $b['sortorder']) ? -1 : 1;
    }
}
