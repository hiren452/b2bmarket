<?php

namespace Matrix\NoncatRfq\Block\Po;

use Ced\RequestToQuote\Model\Source\PoStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfq;
use Matrix\NoncatalogueRfq\Model\RfqPodetail;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

/**
 * Class View
 * @package Ced\RequestToQuote\Block\Adminhtml\Po
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

    protected $addressRepository;

    protected $_addressConfig;

    protected $addressMapper;

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
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
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
        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Ced\RequestToQuote\Model\Po|mixed|null
     */
    public function getPo()
    {
        if (!$this->current_po) {
            if ($currentPo = $this->_coreRegistry->registry('matrix_noncatalog_current_po')) {
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
    /*public function getCustomerAddress($quote_id)
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
    }*/

    /**
     * @return string
     */
    public function getBackUrl()
    {

        return $this->getUrl('vendornoncatrfq/po/index');
    }

    public function getNonCatalogQuote()
    {
        $quote_id = $this->getRequest()->getParam('id');
        $nonCatalofRfq = $this->_quote->load($quote_id);
        return $nonCatalofRfq;
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
    }

    /**
     * @return mixed|null
     */
    public function getStatus()
    {
        $status = $this->getPo()->getData('status');
        return $this->poStatus->getOptionText($status);
    }

    public function getFormattedAddress($addressId)
    {
        if(!isset($addressId) || $addressId<=0) {
            return '';
        }

        try {
            $addressObject = $this->addressRepository->getById($addressId);
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($addressObject));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}
