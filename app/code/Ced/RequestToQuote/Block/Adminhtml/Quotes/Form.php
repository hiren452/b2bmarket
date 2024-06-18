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

namespace Ced\RequestToQuote\Block\Adminhtml\Quotes;

use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory as ItemCollectionFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

/**
 * Class Form
 * @package Ced\RequestToQuote\Block\Adminhtml\Quotes
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var QuoteFactory
     */
    protected $_quote;

    /**
     * @var CollectionFactory|ItemCollectionFactory
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
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var MessageCollectionFactory
     */
    protected $_messageCollectionFactory;

    /**
     * @var null
     */
    protected $_currentQuote = null;

    /**
     * @var null
     */
    protected $quote_id = null;

    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var null
     */
    protected $currentCustomer = null;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * Form constructor.
     * @param Context $context
     * @param Registry $registry
     * @param QuoteFactory $quote
     * @param ItemCollectionFactory $itemCollection
     * @param GroupFactory $customerGroup
     * @param CustomerFactory $customerFactory
     * @param ProductFactory $productFactory
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param StockStateInterface $stockState
     * @param CurrencyFactory $currency
     * @param QuoteStatus $quoteStatus
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QuoteFactory $quote,
        ItemCollectionFactory $itemCollection,
        GroupFactory $customerGroup,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        MessageCollectionFactory $messageCollectionFactory,
        StockStateInterface $stockState,
        CurrencyFactory $currency,
        QuoteStatus $quoteStatus,
        PriceCurrencyInterface $priceFormatter,
        CurrencyModel $currencyModel,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->_quote = $quote;
        $this->_itemCollection = $itemCollection;
        $this->_customerGroup = $customerGroup;
        $this->_customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->_messageCollectionFactory = $messageCollectionFactory;
        $this->stockState = $stockState;
        $this->currency = $currency;
        $this->currencyModel = $currencyModel;
        $this->quoteStatus = $quoteStatus;
        $this->priceFormatter = $priceFormatter;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getQuoteId()
    {
        if (!$this->quote_id) {
            $this->quote_id = $this->getRequest()->getParam('id');
        }
        return $this->quote_id;
    }

    /**
     * @return \Ced\RequestToQuote\Model\ResourceModel\Message\Collection
     */
    public function getMessages()
    {
        return $this->_messageCollectionFactory->create()->addFieldToFilter('quote_id', $this->getQuoteId());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreDetails()
    {
        return $this->_storeManager->getStore()->getName();
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
        $addressdata = $this->getQuoteData();
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
        return $this->_itemCollection->create()
            ->addFieldToFilter('quote_id', $this->getQuoteId())
            ->addFieldToSelect('*');
    }

    /**
     * @return array
     */
    public function getProductId()
    {
        $qproducts = $this->_quotedesc->getCollection()->addFieldToFilter('quote_id', $this->getQuoteId())->addFieldToSelect('product_id');
        foreach ($qproducts->getData() as $value) {
            $prod[] = $value['product_id'];
        }
        return $prod;
    }

    /**
     * @param $product_id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($product_id)
    {
        return $this->productFactory->create()->load($product_id);
    }

    /**
     * @return \Ced\RequestToQuote\Model\Quote|mixed
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
     * @param $product_id
     * @return float
     */
    public function getProductStock($product_id)
    {
        $product = $this->productFactory->create()->load($product_id);
        return $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
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
    public function getPOUrl()
    {

        return $this->getUrl('requesttoquote/po/view', ['quote_id'=>$this->getQuoteId()]);
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
     * @param $price
     * @return float
     */
    public function getPrice($price)
    {
        return $this->priceFormatter->format($price, false, null, null, null);
    }

    public function priceFormat($price)
    {
        if ($price) {
            $price = $this->currencyModel->format(
                $price,
                [
                    'symbol' => $this->getCurrencyCode(),
                    'precision'=> 2
                ],
                false
            );
        }
        return $price;
    }
    /**
     * @return string
     */
    public function getSaveUrl()
    {

        return $this->getUrl('requesttoquote/quotes/save', ['quoteId'=> $this->getQuoteId()]);
    }

    /**
     * @param $optionId
     * @return mixed|null
     */
    public function getQuoteStatus($optionId)
    {
        return $this->quoteStatus->getOptionText($optionId);
    }

    /**
     * @return array
     */
    public function getQuoteStatuses()
    {
        return $this->quoteStatus->getOptionArray();
    }
}
