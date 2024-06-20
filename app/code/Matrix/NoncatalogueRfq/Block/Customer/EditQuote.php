<?php
namespace Matrix\NoncatalogueRfq\Block\Customer;

use Ced\RequestToQuote\Model\Source\PoStatus;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\Message;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfq;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNegotiation\CollectionFactory as RfqNegotiationCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNonMarketVendor\CollectionFactory as rfqNonMarketVendorCollectionFactory;
//use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as rfqProductCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqVendor\CollectionFactory as rfqVendorCollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqPo;

class EditQuote extends Template
{

    /**
     * @var Quote
     */
    protected $_quote;

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

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    protected $poStatus;

    protected $rfqVendorCollectionFactory;

    protected $_vendorFactory;

    protected $_po;

    protected $session;

    protected $rfqNonMarketVendorCollectionFactory;

    protected $timezone;

    public function __construct(
        Context $context,
        NoncatalogRfq $quote,
        rfqVendorCollectionFactory $rfqVendorCollectionFactory,
        rfqNonMarketVendorCollectionFactory $rfqNonMarketVendorCollectionFactory,
        rfqProductCollectionFactory    $rfqProductCollectionFactory,
        RfqNegotiationCollectionFactory $rfqNegotiationCollectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        Session $customerSession,
        RfqPo $po,
        Data $helper,
        CurrencyFactory $currency,
        QuoteStatus $quoteStatus,
        PoStatus $poStatus,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Message $message,
        CategoryFactory $categoryFactory
    ) {
        $this->_quote = $quote;
        $this->rfqVendorCollectionFactory = $rfqVendorCollectionFactory;
        $this->rfqProductCollectionFactory = $rfqProductCollectionFactory;
        $this->rfqNonMarketVendorCollectionFactory =  $rfqNonMarketVendorCollectionFactory;
        $this->rfqNegotiationCollectionFactory = $rfqNegotiationCollectionFactory;
        $this->_po =  $po;
        $this->session = $customerSession;
        $this->poStatus = $poStatus;
        $this->_vendorFactory = $vendorFactory;
        $this->storeManager = $context->getStoreManager();
        $this->helper = $helper;
        $this->currency = $currency;
        $this->quoteStatus = $quoteStatus;
        $this->timezone = $timezone;
        $this->_message = $message;
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

    public function getCustomerId()
    {
        return $this->session->getCustomer()->getId();
    }

    public function getNonCatalogQuote()
    {
        $quote_id = $this->getRequest()->getParam('quoteId');
        $nonCatalofRfq = $this->_quote->load($quote_id);
        return $nonCatalofRfq;
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('noncatalogrequesttoquote/customer/savequote', ['quoteId'=> $this->getRequest()->getParam('quoteId')]);
    }

    public function getNegotiatePostUrl()
    {
        return $this->getUrl('noncatalogrequesttoquote/customer/sendnegotiate', ['quoteId'=> $this->getRequest()->getParam('quoteId')]);
    }

    public function getNegotiateAccepetPostUrl()
    {
        return $this->getUrl('noncatalogrequesttoquote/customer/AccepetNegotiation', ['quoteId'=> $this->getRequest()->getParam('quoteId')]);
    }

    public function getQuoteStatus($status)
    {
        return $this->quoteStatus->getFrontendOptionText($status);
    }
    /**
     * @return string
     */
    public function getNonCatalogQuoteProductJson()
    {
        //$uomOptions =  $this->helper->getUomOptions();
        $resultJson ='';
        $collections = $this->rfqProductCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('quoteId'));
        if ($collections->getSize()) {
            $result = $collections->toArray();
            foreach ($result['items'] as $key => $item) {
                $uomOptions =  $this->helper->getUomOptions($item['rfq_product_id']);
                $catNames = $this->getCategoriesNames($item['category_ids']);
                $item['item_identifier']= ($item['item_identifier']!='') ? $item['item_identifier'] : 'N/A';
                $item['memo']= ($item['memo']!='') ? $item['memo'] : 'N/A';
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

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getSelectedMarketPlaceSuppliers()
    {
        $rfq_id = (int) $this->getRequest()->getParam('quoteId');
        //$collections = $this->rfqVendorCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('quoteId'));
        $collection = $this->_vendorFactory->create()->getCollection()
           ->addAttributeToSelect('name')
           ->addAttributeToSelect('public_name')
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

        $collections = $this->rfqNonMarketVendorCollectionFactory->create()->addFieldToFilter('rfq_id', $this->getRequest()->getParam('quoteId'));
        return $collections;
        //$result = $collections->toArray();
        //return json_encode($result);
    }

    public function getRfqProducts()
    {
        $quote_id = $this->getRequest()->getParam('quoteId');
        $collections = $this->rfqProductCollectionFactory->create()->addFieldToFilter('rfq_id', $quote_id);
        return $collections->getFirstItem();
    }

    public function getFormatedDate($date)
    {
        $dateTimeZone = $this->timezone->date(new \DateTime($date))->format('M d Y');
        return $dateTimeZone;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getChatHistory()
    {
        $quote_id = $this->getRequest()->getParam('quoteId');
        $collection = $this->_message->getCollection()->addFieldtoFilter('quote_id', $quote_id);
        return $collection;
    }

    public function getProposal()
    {
        $quote_id = $this->getRequest()->getParam('quoteId');
        $collection = $this->_po->getCollection()->addFieldtoFilter('rfq_id', $quote_id);
        return $collection;
    }

    public function getPoStatus($status)
    {
        return $this->poStatus->getOptionText($status);
    }

    public function getVendorbyId($id)
    {
        return $this->_vendorFactory->create()->load($id);
    }

    public function getNegotistionHistory()
    {
        $customerId = $this->getCustomerId();
        $quote_id = $this->getRequest()->getParam('quoteId');
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $quote_id)
        ->addFieldToFilter('customer_id', $customerId);
        return $collection;
    }
}
