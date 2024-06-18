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

use Ced\RequestToQuote\Model\PoDetail;
use Ced\RequestToQuote\Model\PoFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\Quote;
use Ced\RequestToQuote\Model\Source\PoStatus;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Group;
use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;

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
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Quote
     */
    protected $quoteResource;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

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
     * @var PriceCurrencyInterface
     */
    private $priceFormatter;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PoFactory $po
     * @param Quote $quoteResource
     * @param QuoteFactory $quoteFactory
     * @param PoDetail $podesc
     * @param Group $custgroup
     * @param ProductFactory $productFactory
     * @param Customer $customerData
     * @param CurrencyFactory $currency
     * @param PoStatus $poStatus
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PoFactory $po,
        Quote $quoteResource,
        QuoteFactory $quoteFactory,
        PoDetail $podesc,
        Group $custgroup,
        Customer $customerData,
        ProductFactory $productFactory,
        CurrencyFactory $currency,
        PoStatus $poStatus,
        PriceCurrencyInterface $priceFormatter,
        CurrencyModel $currencyModel,
        PricingHelper $pricingHelper,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->_po =$po;
        $this->quoteResource =$quoteResource;
        $this->quoteFactory =$quoteFactory;
        $this->productFactory = $productFactory;
        $this->_podesc = $podesc;
        $this->_customerData = $customerData;
        $this->_custgroup = $custgroup;
        $this->currency = $currency;
        $this->poStatus = $poStatus;
        $this->priceFormatter = $priceFormatter;
        $this->currencyModel = $currencyModel;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Ced\RequestToQuote\Model\Po|mixed|null
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
        $quoteModel = $this->quoteFactory->create();
        $this->quoteResource->load($quoteModel, $quote_id);
        $address['country'] = $quoteModel->getCountry();
        $address['state'] = $quoteModel->getState();
        $address['city'] = $quoteModel->getCity();
        $address['pincode'] = $quoteModel->getPincode();
        $address['street'] = $quoteModel->getStreet();
        $address['telephone'] = $quoteModel->getTelephone();
        return $address;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {

        return $this->getUrl('requesttoquote/po/index');
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
     * @param $price
     * @return float
     */
    public function getPrice($price)
    {
        return $this->priceFormatter->format($price, false, null, null, null);
    }

    /**
     * @param $price
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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
     * @return mixed|null
     */
    public function getStatus()
    {
        $status = $this->getPo()->getData('status');
        return $this->poStatus->getOptionText($status);
    }

    public function getQuoteIncrementId($quote_id)
    {
        $quoteModel = $this->quoteFactory->create();
        $this->quoteResource->load($quoteModel, $quote_id);
        return $quoteModel->getQuoteIncrementId();
    }
}
