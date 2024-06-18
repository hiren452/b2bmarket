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
namespace Ced\RequestToQuote\Block;

use Ced\RequestToQuote\Helper\Data as Helper;
use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Block\Data;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\Currency;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class QuoteReview
 * @package Ced\RequestToQuote\Block
 */
class QuoteReview extends Template
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Currency
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $_country;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var CollectionFactory
     */
    protected $requestQuoteCollectionFactory;

    /**
     * @var array
     */
    protected $itemCollection = [];

    /**
     * @var int
     */
    protected $subtotal = 0;

    /**
     * QuoteReview constructor.
     * @param ProductFactory $productFactory
     * @param Context $context
     * @param Session $customerSession
     * @param Image $imageHelper
     * @param Data $country
     * @param Currency $localeCurrency
     * @param Helper $helper
     * @param CurrencyFactory $currency
     * @param CollectionFactory $requestQuoteCollectionFactory
     * @param array $data
     */
    public function __construct(
        ProductFactory $productFactory,
        Context $context,
        Session $customerSession,
        Image $imageHelper,
        Data $country,
        Currency $localeCurrency,
        Helper $helper,
        CurrencyFactory $currency,
        CollectionFactory $requestQuoteCollectionFactory,
        DirectoryHelper $directoryHelper,
        TaxHelper $taxHelper,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->_localeCurrency = $localeCurrency;
        $this->storeManager = $context->getStoreManager();
        $this->_country = $country;
        $this->helper = $helper;
        $this->_customerSession = $customerSession;
        $this->currency = $currency;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        $this->directoryHelper = $directoryHelper;
        $this->taxHelper = $taxHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return array|\Ced\RequestToQuote\Model\ResourceModel\RequestQuote\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        if (!$this->itemCollection) {
            $this->itemCollection = $this->requestQuoteCollectionFactory->create()
                                    ->addFieldToFilter('customer_id', $this->getId())
                                    ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId());
        }
        return $this->itemCollection;
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        return $this->_customerSession->getCustomer()->getName();
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_customerSession->getCustomer()->getEmail();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_customerSession->getCustomer()->getId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubtotal()
    {
        if (!$this->subtotal) {
            $items = $this->getItems();
            foreach ($items as $item) {
                $this->subtotal += ($item->getQuoteQty() * $item->getQuotePrice());
            }
        }
        return sprintf("%.2f", $this->subtotal);
    }

    /**
     * @return string
     */
    public function getCountryCollection()
    {
        return $this->_country->getCountryHtmlSelect();
    }

    /**
     * @return int|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemsCount()
    {
        return count($this->getItems());
    }

    /**
     * @param $amount
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Currency_Exception
     */
    public function getToCurrency($amount)
    {
        return $this->_localeCurrency->getCurrency($this->storeManager->getStore()->getBaseCurrencyCode())->toCurrency($amount);
    }

    /**
     * @return mixed|void
     */
    public function getAddress()
    {
        $address = $this->_customerSession->getData('address');
        if(isset($address)) {
            return $address;
        } else {
            return;
        }
    }

    /**
     * @return int|mixed|null
     */
    public function getRegionId()
    {
        $address = $this->getAddress();
        if(!empty($address)) {
            $region = $address['region_id'];
            return $region === null ? 0 : $address['region_id'];
        }
        return null;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrency()
    {
        $code =  $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->load($code)->getCurrencySymbol();
    }

    /**
     * @param $productid
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($productid)
    {
        return $this->productFactory->create()->load($productid);
    }

    /**
     * @param $product
     * @return string
     */
    public function getImage($product)
    {
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }
}
