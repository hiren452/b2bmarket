<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Block\Po;

use Ced\RequestToQuote\Model\PoDetail;
use Ced\RequestToQuote\Model\PoFactory;
use Ced\RequestToQuote\Model\Quote;
use Ced\RequestToQuote\Model\Source\PoStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Ced\CsRfq\Block\Adminhtml\Po
 */
class View extends Template
{
    /**
     * @var null
     */
    protected $current_po = null;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PoFactory
     */
    protected $_po;

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var Customer
     */
    protected $_customerData;

    /**
     * @var Group
     */
    protected $_custgroup;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var PoStatus
     */
    protected $poStatus;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PoFactory $po
     * @param Quote $quote
     * @param PoDetail $podesc
     * @param Group $custgroup
     * @param Customer $customerData
     * @param CurrencyFactory $currency
     * @param PoStatus $poStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PoFactory $po,
        Quote $quote,
        PoDetail $podesc,
        Group $custgroup,
        Customer $customerData,
        CurrencyFactory $currency,
        PoStatus $poStatus,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_po =$po;
        $this->_quote =$quote;
        $this->_podesc = $podesc;
        $this->_customerData = $customerData;
        $this->_custgroup = $custgroup;
        $this->currency = $currency;
        $this->poStatus = $poStatus;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getPo()
    {
        if (!$this->current_po) {
            if ($currentPo = $this->_coreRegistry->registry('current_po')) {
                $this->current_po = $currentPo;
            } else {
                $this->current_po = $this->_po->create($this->getRequest()->getParam('id'));
            }
        }
        return $this->current_po;
    }

    /**
     * @param $customer_id
     * @return Customer
     */
    public function getCustomer($customer_id)
    {
        return $this->_customerData->load($customer_id);
    }

    /**
     * @param $customer
     * @return string
     */
    public function getCustomerGroup($customer)
    {
        $customergrp = $customer->getGroupId();
        return $this->_custgroup->load($customergrp)->getCustomerGroupCode();
    }

    /**
     * @param $quote_id
     * @return array
     */
    public function getCustomerAddress($quote_id)
    {
        $address = [];
        $addressdata = $this->_quote->load($quote_id);
        $address['country'] = $addressdata->getCountry();
        $address['state'] = $addressdata->getState();
        $address['city'] = $addressdata->getCity();
        $address['pincode'] = $addressdata->getPincode();
        $address['street'] = $addressdata->getAddress();
        $address['telephone'] = $addressdata->getTelephone();
        return $address;
    }

    /**
     * @return mixed
     */
    public function getBackUrl()
    {
        return $this->getUrl('rfq/po/index');
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        $code =  $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->load($code)->getCurrencySymbol();
    }

    /**
     * @param $po_increment_id
     * @param $quote_id
     * @return mixed
     */
    public function getPoData($po_increment_id, $quote_id)
    {
        return $this->_podesc->getCollection()->addFieldToFilter('quote_id', $quote_id)->addFieldToFilter('po_id', $po_increment_id);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        $status = $this->getPo()->getData('status');
        return $this->poStatus->getOptionText($status);
    }
}
