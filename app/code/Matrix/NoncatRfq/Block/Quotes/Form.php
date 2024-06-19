<?php

namespace Matrix\NoncatRfq\Block\Quotes;

use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Customer\Api\AddressRepositoryInterface;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\Message;

use Matrix\NoncatalogueRfq\Model\NoncatalogRfq;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNegotiation\CollectionFactory as RfqNegotiationCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNonMarketVendor\CollectionFactory as rfqNonMarketVendorCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as rfqProductCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqVendor\CollectionFactory as rfqVendorCollectionFactory;
//use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
//use Ced\RequestToQuote\Model\Source\PoStatus;
use Matrix\NoncatalogueRfq\Model\RfqPo;
use Matrix\NoncatalogueRfq\Model\RfqPodetail;

class Form extends Template
{

    /**
    * @var Quote
    */
    protected $_quote;

    /**
     * @var Po
     */
    protected $_po;

    /**
     * @var Po
     */
    protected $_podetail;

    /**
    * @var CollectionFactory
    */
    protected $rfqProductCollectionFactory;

    protected $rfqNegotiationCollectionFactory;

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

    protected $priceHelper;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    protected $session;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    protected $rfqVendorCollectionFactory;

    protected $_vendorFactory;

    protected $rfqNonMarketVendorCollectionFactory;

    protected $_customerFactory;

    protected $currentCustomer = null;

    /**
    * @var AddressRepositoryInterface
    */
    protected $addressRepository;

    protected $_addressConfig;

    protected $addressMapper;
    /**
     * ListQuotes constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param QuoteStatus $quoteStatus
     */
    public function __construct(
        Context $context,
        NoncatalogRfq $quote,
        Message $message,
        rfqVendorCollectionFactory $rfqVendorCollectionFactory,
        rfqNonMarketVendorCollectionFactory $rfqNonMarketVendorCollectionFactory,
        rfqProductCollectionFactory	$rfqProductCollectionFactory,
        RfqNegotiationCollectionFactory $rfqNegotiationCollectionFactory,
        RfqPo $po,
        RfqPodetail $podetail,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        Session $customerSession,
        Data $helper,
        PriceHelper $priceHelper,
        CurrencyFactory $currency,
        QuoteStatus $quoteStatus,
        CustomerFactory $customerFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        CategoryFactory $categoryFactory
    ) {
        $this->_quote = $quote;
        $this->_message = $message;
        $this->rfqVendorCollectionFactory = $rfqVendorCollectionFactory;
        $this->rfqProductCollectionFactory = $rfqProductCollectionFactory;
        $this->rfqNonMarketVendorCollectionFactory =  $rfqNonMarketVendorCollectionFactory;
        $this->rfqNegotiationCollectionFactory = $rfqNegotiationCollectionFactory;
        $this->session = $customerSession;
        $this->_vendorFactory = $vendorFactory;
        $this->storeManager = $context->getStoreManager();
        $this->helper = $helper;
        $this->priceHelper = $priceHelper;
        $this->_customerFactory = $customerFactory;
        $this->_po = $po;
        $this->_podetail = $podetail;

        $this->addressRepository = $addressRepository;
        $this->_addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;

        $this->currency = $currency;
        $this->quoteStatus = $quoteStatus;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    /**
    *
    */
    public function _construct()
    {
        $this->setTemplate('customer/editquote.phtml');
    }

    public function getNonCatalogQuote()
    {
        $quote_id = $this->getRequest()->getParam('id');
        $nonCatalofRfq = $this->_quote->load($quote_id);
        return $nonCatalofRfq;
    }

    public function getbackUrl()
    {
        //return $this->getUrl('vendornoncatrfq/rfq/view', ['id'=> $this->getRequest()->getParam('id')]);
        return $this->getUrl('vendornoncatrfq/rfq/index/');
    }

    /**
    * @return string
    */
    public function getSendUrl()
    {
        return $this->getUrl('vendornoncatrfq/rfq/savequotes', ['id'=> $this->getRequest()->getParam('id')]);
    }

    public function getPoCreateUrl()
    {
        return $this->getUrl('vendornoncatrfq/po/view/', ['id'=> $this->getRequest()->getParam('id')]);
    }

    /**
     * @return string
     */
    public function getNonCatalogQuoteProductJson()
    {
        //$uomOptions =  $this->helper->getUomOptions();
        $resultJson ='';
        $collections = $this->rfqProductCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('id'));
        if($collections->getSize()) {
            $result = $collections->toArray();
            foreach($result['items'] as $key=>$item) {
                $uomOptions =  $this->helper->getUomOptions($item['rfq_product_id']);
                $catNames = $this->getCategoriesNames($item['category_ids']);
                $item['uom'] = $uomOptions[$item['umo']];
                $item['categories'] = $catNames;
                $item['uploads'] = $this->getUplaodFiles($item['uploads']);
                $item['target_price'] = $this->priceHelper->currency($item['target_price'], true, false);
                $result['items'][$key] = $item;
            }
            $pdfThumb =$this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'rfq-noncataog-uploads/pdf.png';
            $result['pdfThumb'] = $pdfThumb;
            $resultJson = json_encode($result);
        }
        return $resultJson;

    }

    public function getNegotiatePostUrl()
    {
        return $this->getUrl('vendornoncatrfq/rfq/sendnegotiate', ['id'=> $this->getRequest()->getParam('id')]);
    }

    public function getNegotiateAccepetPostUrl()
    {
        return $this->getUrl('vendornoncatrfq/rfq/accepetnegotiation', ['id'=> $this->getRequest()->getParam('id')]);
    }

    public function getPOUrl()
    {
        return $this->getUrl('vendornoncatrfq/po/save', ['id'=> $this->getRequest()->getParam('id')]);
    }

    public function getRfqProducts()
    {
        $collections = $this->rfqProductCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('id'));
        return $collections->getFirstItem();
    }

    public function getCategoriesNames($cat_Ids)
    {
        $arrCatdIds =  explode(",", $cat_Ids);
        $names = '';
        foreach($arrCatdIds as $key=>$val) {
            $category = $this->getCategoryById($val);
            $names.= $category->getName();
        }
        return $names;
    }

    public function getCategoryById($catdId)
    {
        return $this->categoryFactory->create()->load($catdId);
    }

    public function getUplaodFiles($uploads)
    {
        $files = json_decode($uploads);
        if(!is_array($files)) {
            return '';
        }
        if(count($files)<=0) {
            return '';
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $arrUploas = [];

        foreach($files as $file) {
            $processedName = preg_replace('/\s+/', '_', $file->fileName);

            if($file->fileExt =="pdf") {
                $file->fileThumb	= $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'rfq-noncataog-uploads/pdf.png';
            } else {
                $file->fileThumb	= $mediaUrl . 'rfq-noncataog-uploads/' . $processedName;
            }
            $file->filePath	= $mediaUrl . 'rfq-noncataog-uploads/' . $processedName;
            $arrUploas[] = $file;
        }

        return json_encode($arrUploas);
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getSelectedMarketPlaceSuppliers()
    {
        $rfq_id = (int) $this->getRequest()->getParam('id');
        //$collections = $this->rfqVendorCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('quoteId'));
        $collection = $this->_vendorFactory->create()->getCollection()
           ->addAttributeToSelect('name')
           ->addAttributeToSelect('email')
           ->addAttributeToSelect('group')
           ->addAttributeToSelect('status')
           ->addAttributeToFilter('status', ['eq'=>'approved']);
        $noncatrfq_vendor_tbl = 'matrix_noncatalog_rfq_vendor';
        $collection->getSelect()->joinInner($noncatrfq_vendor_tbl, 'e.entity_id = ' . $noncatrfq_vendor_tbl . '.vendor_id AND ' . $noncatrfq_vendor_tbl . '.rfq_id = ' . $rfq_id, ['is_emailsend ']);
        return $collection;
    }

    public function getnonMarketVendorCollection()
    {

        $collections = $this->rfqNonMarketVendorCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('id'));
        return $collections;
        //$result = $collections->toArray();
        //return json_encode($result);
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getChatHistory()
    {
        $chatData = $this->_message->getCollection()->addFieldtoFilter('quote_id', $this->getRequest()->getParam('id'));
        return $chatData;
    }

    public function getNegotistionHistory()
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $this->getRequest()->getParam('id'))
        ->addFieldToFilter('vendor_id', $vendor_id);
        return $collection;
    }

    public function getNegotistionHistoryById($id)
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('id', $id);
        return $collection;
    }

    public function isNegotiationAccpected()
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $this->getRequest()->getParam('id'))
        ->addFieldToFilter('vendor_id', $vendor_id)
        ->addFieldToFilter('is_accpected', ['eq'=>1]);
        $isAccpected =  ($collection->getSize()) ? true : false;
        return $isAccpected;
    }

    public function getAccpectedNegotiationId()
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $this->getRequest()->getParam('id'))
        ->addFieldToFilter('vendor_id', $vendor_id)
        ->addFieldToFilter('is_accpected', ['eq'=>1]);
        if($collection->getSize()<=0) {
            return null;
        }

        return  $collection->getFirstItem()->getId();

    }

    public function getVendorId()
    {
        //return $this->session->getCustomer()->getId();
        return $this->session->getVendorId();
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

    public function getCustomerAddress()
    {
        /*$address = [];
        $addressdata = $this->_currentQuote;
        $address['country'] = $addressdata->getCountry();
        $address['state'] = $addressdata->getState();
        $address['city'] = $addressdata->getCity();
        $address['pincode'] = $addressdata->getPincode();
        $address['street'] = $addressdata->getAddress();
        $address['telephone'] = $addressdata->getTelephone();
        return $address;*/
    }

    public function getStatus($status)
    {
        return $this->quoteStatus->getOptionText($status);
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

    /**
         * @param $vendor_id
         * @param $quote_id
         * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
         */
    public function getPoData($vendor_id, $rfq_id)
    {
        $noncatrfq_po_details_tbl = 'matrix_noncatalog_po_details';
        $collection = $this->_po->getCollection()
        ->addFieldToFilter('main_table.rfq_id', $rfq_id)
        ->addFieldToFilter('main_table.vendor_id', $vendor_id);
        $collection->getSelect()->join(
            $noncatrfq_po_details_tbl,
            'main_table.rfq_id = ' . $noncatrfq_po_details_tbl . '.quote_id',
            ['*']
        );
        //echo $collection->getSelect();
        return $collection;
    }

}
