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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Block\Adminhtml\Po;

use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory as ItemCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;

class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var null
     */
    protected $quote_id = null;

    /**\Magento\Framework\View\Element\
     * @var null
     */
    protected $_currentQuote = null;

    /**
     * @var null
     */
    protected $currentCustomer = null;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var QuoteFactory
     */
    protected $_quote;

    /**
     * @var ItemCollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var GroupFactory
     */
    protected $_customerGroup;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param QuoteFactory $quote
     * @param ItemCollectionFactory $itemCollection
     * @param GroupFactory $customerGroup
     * @param CustomerFactory $customerFactory
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param CurrencyFactory $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QuoteFactory $quote,
        ItemCollectionFactory $itemCollection,
        GroupFactory $customerGroup,
        CustomerFactory $customerFactory,
        CurrencyFactory $currency,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->_quote = $quote;
        $this->_itemCollection = $itemCollection;
        $this->_customerGroup = $customerGroup;
        $this->_customerFactory = $customerFactory;
        $this->currency = $currency;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getQuoteId()
    {
        if (!$this->quote_id) {
            $this->quote_id = $this->getRequest()->getParam('quote_id');
        }
        return $this->quote_id;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getQuoteData()->getCustomerId();
    }

    /**
     * @param $customer_id
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomer($customer_id)
    {
        if (!$this->currentCustomer) {
            $this->currentCustomer = $this->_customerFactory->create()->load($customer_id);
        }
        return $this->currentCustomer;
    }

    /**
     * @param $customer_id
     * @return string
     */
    public function getCustomerGroup($customer_id)
    {
        $customergrp = $this->getCustomer($customer_id)->getGroupId();
        return $this->_customerGroup->create()->load($customergrp)->getCustomerGroupCode();
    }

    /**
     * @return array
     */
    public function getCustomerAddress()
    {
        $address = [];
        $addressdata = $this->_currentQuote;
        $address['country'] = $addressdata->getCountry();
        $address['state'] = $addressdata->getState();
        $address['city'] = $addressdata->getCity();
        $address['pincode'] = $addressdata->getPincode();
        $address['street'] = $addressdata->getAddress();
        $address['telephone'] = $addressdata->getTelephone();
        return $address;
    }

    /**
     * @return \Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\Collection
     */
    public function getItems()
    {
        return $this->_itemCollection->create()->addFieldToFilter('quote_id', $this->getQuoteId())->addFieldToSelect('*');
    }

    /**
     * @return \Ced\RequestToQuote\Model\Quote|mixed|null
     */
    public function getQuoteData()
    {
        if (!$this->_currentQuote) {
            if ($quote = $this->_coreRegistry->registry('current_quote')) {
                $this->_currentQuote = $quote;
            } else {
                $this->_currentQuote = $this->_quote->create()->load($this->getQuoteId());
            }
        }
        return $this->_currentQuote;
    }

    /**
     * @return string
     */
    public function getPOUrl()
    {

        return $this->getUrl('requesttoquote/po/save', ['quote_id'=>$this->getQuoteId()]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {

        return $this->getUrl('requesttoquote/quotes/view', ['quote_id'=>$this->getQuoteId()]);
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {

        return $this->getUrl('requesttoquote/quotes/view');
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
}
