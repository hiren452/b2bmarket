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

namespace Ced\RequestToQuote\Block\Cart;

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory as RequestQuoteCollection;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Sidebar
 * @package Ced\RequestToQuote\Block\Cart
 */
class Sidebar extends Template
{
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var int
     */
    protected $_totalQuoteQty = 0;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestQuoteCollection
     */
    protected $requestQuoteCollection;

    /**
     * @var null
     */
    protected $currentCustomerQuote = null;

    /**
     * Sidebar constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param Image $imageHelper
     * @param Data $helper
     * @param ProductFactory $productFactory
     * @param PricingHelper $pricingHelper
     * @param CurrencyFactory $currency
     * @param RequestQuoteCollection $requestQuoteCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Image $imageHelper,
        Data $helper,
        ProductFactory $productFactory,
        PricingHelper $pricingHelper,
        CurrencyFactory $currency,
        RequestQuoteCollection $requestQuoteCollection,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_session = $customerSession;
        $this->imageHelper = $imageHelper;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->pricingHelper = $pricingHelper;
        $this->currency = $currency;
        $this->storeManager = $context->getStoreManager();
        $this->requestQuoteCollection = $requestQuoteCollection;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_session->isLoggedIn();
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->_session->getCustomer()->getGroupId();
    }

    /**
     * @return string
     */
    public function getQuoteCartUrl()
    {
        return $this->getUrl('requesttoquote/cart/index');
    }

    /**
     * @return \Ced\RequestToQuote\Model\ResourceModel\RequestQuote\Collection
     */
    public function getRequestQuoteData()
    {
        if (!$this->currentCustomerQuote) {
            $this->currentCustomerQuote = $this->requestQuoteCollection->create()
                                          ->addFieldToFilter('customer_id', $this->_session->getCustomer()->getId())
                                          ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId());
        }
        return $this->currentCustomerQuote;
    }

    /**
     * @param $productid
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($productid)
    {
        return $this->productFactory->create()->load($productid);

    }

    public function getItemCount()
    {
        if (!$this->_totalQuoteQty) {
            $totalItemCount = 0;
            foreach ($this->getRequestQuoteData() as $item) {
                $totalItemCount += $item->getQuoteQty();
            }
            $this->_totalQuoteQty = (int)$totalItemCount;
        }
        return $this->_totalQuoteQty;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function getFormattedPrice($price)
    {
        return  $this->pricingHelper->currency($price, true, false);
    }

    /**
     * @return float|string
     */
    public function getQuoteSubtotal()
    {
        $subtotal = 0;
        foreach($this->getRequestQuoteData() as $item) {
            $subtotal+= $item->getQuotePrice() * $item->getQuoteQty();
        }
        return  $this->pricingHelper->currency($subtotal, true, false);
    }

    /**
     * @param $product
     * @return string
     */
    public function getImage($product)
    {
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }

    /**
     * @return array
     */
    public function getAllowedCustomerGroups()
    {
        $value = $this->helper->getConfigValue('requesttoquote_configuration/active/custgroups');
        $custgroups = explode(',', $value);
        return $custgroups;
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
