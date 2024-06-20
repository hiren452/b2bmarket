<?php

namespace Matrix\NoncatalogueRfq\Block\Adminhtml\Po;

use Ced\RequestToQuote\Model\Source\PoStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

//use Ced\RequestToQuote\Model\RfqPoFactory;
use Magento\Customer\Model\Customer;

//use Ced\RequestToQuote\Model\Quote;
use Magento\Customer\Model\Group;

//use Ced\RequestToQuote\Model\PoDetail;
use Magento\Directory\Model\CurrencyFactory;

use Magento\Framework\Registry;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfq;
use Matrix\NoncatalogueRfq\Model\RfqPodetail;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

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
        RfqPoFactory $po,
        NoncatalogRfq $quote,
        RfqPodetail $podesc,
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
     * @return \Ced\RequestToQuote\Model\Po|mixed|null
     */
    public function getPo()
    {
        if (!$this->current_po) {
            if ($currentPo = $this->_coreRegistry->registry('matrix_current_po')) {
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
    public function getCustomerAddress()
    {
        $address = [];
        /*$addressdata = $this->getQuoteData();
        $address['country'] = $addressdata->getCountry();
        $address['state'] = $addressdata->getState();
        $address['city'] = $addressdata->getCity();
        $address['pincode'] = $addressdata->getPincode();
        $address['street'] = $addressdata->getAddress();
        $address['telephone'] = $addressdata->getTelephone();*/

        $address['country'] = 'US';
        $address['state'] = 'La';
        $address['city'] = 'Fake City';
        $address['pincode'] = '844441';
        $address['street'] = 'Fake street';
        $address['telephone'] = '666666';
        return $address;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {

        return $this->getUrl('noncatalogrfq/po/index');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {

        $code =  $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->load($code)->getCurrencySymbol();
    }

    /**
     * @param $po_increment_id
     * @param $quote_id
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getPoData($po_increment_id, $quote_id)
    {
        return $this->_podesc->getCollection()->addFieldToFilter('quote_id', $quote_id)->addFieldToFilter('po_id', $po_increment_id);
        /*$collection = $this->_podesc->getCollection()->addFieldToFilter('quote_id',$quote_id)->addFieldToFilter('po_id', $po_increment_id);
        echo $collection->getSelect();
        return $collection;*/
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        $status = $this->getPo()->getData('status');
        return $this->poStatus->getOptionText($status);
    }
}
