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

namespace Ced\RequestToQuote\Block\Customer;

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Message;
use Ced\RequestToQuote\Model\Quote;
use Ced\RequestToQuote\Model\QuoteDetail;
use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
use Ced\RequestToQuote\Model\Source\PoStatus;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ProductFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class EditQuote
 * @package Ced\RequestToQuote\Block\Customer
 */
class EditQuote extends Template
{

    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var QuoteDetail
     */
    protected $_quoteDetail;

    /**
     * @var Message
     */
    protected $_message;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var PoCollectionFactory
     */
    protected $poCollectionFactory;

    /**
     * @var null
     */
    protected $poCollection = null;

    /**
     * @var PoStatus
     */
    protected $poStatus;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * EditQuote constructor.
     * @param Context $context
     * @param Quote $quote
     * @param QuoteDetail $quoteDetail
     * @param Message $message
     * @param ProductFactory $productFactory
     * @param Data $helper
     * @param CurrencyFactory $currency
     * @param Image $imageHelper
     * @param PoCollectionFactory $poCollectionFactory
     * @param PoStatus $poStatus
     * @param QuoteStatus $quoteStatus
     */
    public function __construct(
        Context $context,
        Quote $quote,
        QuoteDetail $quoteDetail,
        Message $message,
        ProductFactory $productFactory,
        Data $helper,
        CurrencyFactory $currency,
        Image $imageHelper,
        PoCollectionFactory $poCollectionFactory,
        PoStatus $poStatus,
        QuoteStatus $quoteStatus
    ) {
        $this->_quote = $quote;
        $this->_quoteDetail = $quoteDetail;
        $this->_message = $message;
        $this->storeManager = $context->getStoreManager();
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->currency = $currency;
        $this->imageHelper = $imageHelper;
        $this->poCollectionFactory = $poCollectionFactory;
        $this->poStatus = $poStatus;
        $this->quoteStatus = $quoteStatus;
        parent::__construct($context);
    }

    /**
     *
     */
    public function _construct()
    {
        $this->setTemplate('customer/editquote.phtml');
        $quoteModel = $this->_quoteDetail->getCollection()->addFieldtoFilter('quote_id', $this->getRequest()->getParam('quoteId'));
        $this->setCollection($quoteModel);
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager')->setLimit(5)->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->getTitle()->set("#" . $this->_quote->load($this->getRequest()->getParam('quoteId'))->getQuoteIncrementId());
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('requesttoquote/customer/savequotes', ['quoteId'=> $this->getRequest()->getParam('quoteId')]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('requesttoquote/customer/quotes');
    }

    /**
     * @param $product_id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($product_id)
    {
        $product = $this->productFactory->create()->load($product_id);
        return $product;
    }

    /**
     * @param $product
     * @return string
     */
    public function getProductImage($product)
    {
        return $this->imageHelper->init($product, 'product_base_image')->getUrl();
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getChatHistory()
    {
        $chatData = $this->_message->getCollection()->addFieldtoFilter('quote_id', $this->getRequest()->getParam('quoteId'));
        return $chatData;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_quote->load($this->getRequest()->getParam('quoteId'));
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
     * @return \Ced\RequestToQuote\Model\ResourceModel\Po\Collection|null
     */
    public function getPoCollection()
    {
        if (!$this->poCollection) {
            return $this->poCollectionFactory->create()->addFieldToFilter('quote_id', $this->getRequest()->getParam('quoteId'));
        }
        return $this->poCollection;
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public function getPoStatus($status)
    {
        return $this->poStatus->getOptionText($status);
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public function getQuoteStatus($status)
    {
        return $this->quoteStatus->getFrontendOptionText($status);
    }
}
