<?php

namespace Matrix\NoncatalogueRfq\Block\Adminhtml\Po;

use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Backend\Block\Template\Context;

//use Ced\RequestToQuote\Model\QuoteFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;

//use Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory as ItemCollectionFactory;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\GroupFactory;
//use Ced\RequestToQuote\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Directory\Model\CurrencyFactory;

use Magento\Framework\Registry;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as ItemCollectionFactory;

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
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $categoryFactory;

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
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        NoncatalogRfqFactory $quote,
        ItemCollectionFactory $itemCollection,
        GroupFactory $customerGroup,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        MessageCollectionFactory $messageCollectionFactory,
        StockStateInterface $stockState,
        CurrencyFactory $currency,
        QuoteStatus $quoteStatus,
        Data $helper,
        CategoryFactory $categoryFactory,
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
        $this->quoteStatus = $quoteStatus;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->categoryFactory = $categoryFactory;
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
     * @return \Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\Collection
     */
    public function getItems()
    {
        return $this->_itemCollection->create()
                    ->addFieldToFilter('rfq_id', $this->getQuoteId())
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

    public function getNonCatalogQuoteProductJson()
    {

        $uomOptions =  $this->helper->getUomOptions();

        $resultJson ='';
        $collections = $this->_itemCollection->create()->addFieldToFilter('rfq_id', $this->getQuoteId());
        if ($collections->getSize()) {
            $result = $collections->toArray();

            foreach ($result['items'] as $key => $item) {
                $catNames = $this->getCategoriesNames($item['category_ids']);

                $item['uom'] = $uomOptions[$item['umo']];
                $item['categories'] = $catNames;
                $item['uploads'] = $this->getUplaodFiles($item['uploads']);
                $result['items'][$key] = $item;
            }
            $pdfThumb =$this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'rfq-noncataog-uploads/pdf.png';
            $result['pdfThumb'] = $pdfThumb;
            $resultJson = json_encode($result);
        }
        return $resultJson;
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getUplaodFiles($uploads)
    {
        $files = json_decode($uploads);
        if (!is_array($files)) {
            return '';
        }
        if (count($files)<=0) {
            return '';
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $arrUploas = [];

        foreach ($files as $file) {
            if ($file->fileExt =="pdf") {
                $file->fileThumb    = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'rfq-noncataog-uploads/pdf.png';
            } else {
                $file->fileThumb    = $mediaUrl . 'rfq-noncataog-uploads/' . $file->fileName;
            }
            $file->filePath    = $mediaUrl . 'rfq-noncataog-uploads/' . $file->fileName;
            $arrUploas[] = $file;
        }

        return json_encode($arrUploas);
    }

    public function getCategoriesNames($cat_Ids)
    {
        $arrCatdIds =  explode(",", $cat_Ids);
        $names = '';
        foreach ($arrCatdIds as $key => $val) {
            $category = $this->getCategoryById($val);
            $names.= $category->getName();
        }
        return $names;
    }

    public function getCategoryById($catdId)
    {
        return $this->categoryFactory->create()->load($catdId);
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

        //return $this->getUrl('noncatalogrfq/quotes/view',array('quote_id'=>$this->getQuoteId()));
        return $this->getUrl('noncatalogrfq/rfq/index');
    }

    /**
     * @return string
     */
    public function getPOUrl()
    {

        return $this->getUrl('noncatalogrfq/po/view', ['quote_id'=>$this->getQuoteId()]);
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
     * @return string
     */
    public function getSaveUrl()
    {

        return $this->getUrl('noncatalogrfq/rfq/save', ['id'=> $this->getQuoteId()]);
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
