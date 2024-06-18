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

use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory as ItemCollectionFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Registry;

class Form extends \Magento\Backend\Block\Template
{

    /**\Magento\Framework\View\Element\
     * @var null
     */
    protected $_currentQuote = null;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
    * @var null
    */
    protected $currentCustomer = null;

    /**
     * @var QuoteFactory
     */
    protected $_quote;

    /**
     * @var GroupFactory
     */
    protected $_customerGroup;

    /**
     * @var ItemCollectionFactory
     */
    protected $_itemCollection;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var null
     */
    protected $quote_id = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QuoteFactory $quote
     * @param ItemCollectionFactory $itemCollection
     * @param GroupFactory $customerGroup
     * @param CustomerFactory $customerFactory
     * @param CurrencyFactory $currency
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        Context $context,
        ItemCollectionFactory $itemCollection,
        QuoteFactory $quote,
        CustomerFactory $customerFactory,
        CurrencyFactory $currency,
        GroupFactory $customerGroup,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerGroup = $customerGroup;
        $this->_itemCollection = $itemCollection;
        $this->currency = $currency;
        $this->_coreRegistry = $registry;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->getQuoteData()->getCustomerId();
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
     * @param $customerId
     * @return string
     */
    public function getCustomerGroup($customerId)
    {
        $customergrp = $this->getCustomer($customerId)->getGroupId();
        return $this->_customerGroup->create()->load($customergrp)->getCustomerGroupCode();
    }

    /**
     * @param $customerId
     * @return \Magento\Customer\Model\Customer|null
     */
    public function getCustomer($customerId)
    {
        if (!$this->currentCustomer) {
            $this->currentCustomer = $this->_customerFactory->create()->load($customerId);
        }
        return $this->currentCustomer;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->_itemCollection->create()->addFieldToFilter('quote_id', $this->getQuoteId())->addFieldToSelect('*');
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
     * @return mixed|null
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
    public function getBackUrl()
    {
        return $this->getUrl('rfq/quotes/view', ['id'=>$this->getQuoteId()]);
    }

    /**
     * @return string
     */
    public function getPOUrl()
    {
        return $this->getUrl('rfq/po/save', ['quote_id' => $this->getQuoteId()]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        $code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->load($code)->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('rfq/quotes/index');
    }

}
