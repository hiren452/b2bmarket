<?php

namespace Matrix\NoncatalogueRfq\Block\Customer;

use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq\CollectionFactory;

class ListQuotes extends \Magento\Framework\View\Element\Template
{
    /**
     * Quote items per page.
     * @var int
     */
    private $itemsPerPage = 10;

    /**
     * @var CollectionFactory
     */
    protected $quoteCollection;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * @var RfqTemplateFactory
     */
    protected $_rfqtemplateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * ListQuotes constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param QuoteStatus $quoteStatus
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqtemplateFactory,
        QuoteStatus $quoteStatus
    ) {
        $this->session = $customerSession;
        $this->quoteCollection = $collectionFactory;
        $this->quoteStatus = $quoteStatus;
        $this->_rfqtemplateFactory = $rfqtemplateFactory;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context);
        if ($this->getRequest()->getParam('limit')) {
            $this->itemsPerPage = $this->getRequest()->getParam('limit');
        }
    }

    public function _construct()
    {
        $this->setTemplate('customer/listquotes.phtml');
        $this->getUrl();
        $customer_Id = $this->session->getCustomerId();
        $quoteModel = $this->quoteCollection->create()
            ->addFieldtoFilter('customer_id', ['customer_id' => $customer_Id])
            ->setOrder('rfq_id', 'DESC');

        $this->setCollection($quoteModel);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager')->setLimit($this->itemsPerPage)->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->getTitle()->set("My Non-catalog Quotes");
        //echo $this->getCollection()->getSelect();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public function getStatus($status)
    {
        return $this->quoteStatus->getFrontendOptionText($status);
    }

    public function getRfqTemplateCollection()
    {
        //$customer_Id = $this->session->getCustomerId();
        $customer_Id = $this->getCustomerId();

        if ($customer_Id<=0) {
            return null;
        }

        $collection = $this->_rfqtemplateFactory->create()->getCollection()
        ->addFieldToSelect('id')
         ->addFieldToSelect('template_name')
        ->addFieldToFilter('customer_id', ['eq'=>$customer_Id]);
        return $collection;
    }

    public function getRfqTemplateCollectionJson()
    {
        $collection  =  $this->getRfqTemplateCollection();

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $arr[] = ['value'=>$item->getID(),'label'=>$item->getTemplateName()];
            }
            return json_encode($arr);
        } else {
            $arr[]= ['value'=>null,'label'=>__('Please select')];
            return json_encode($arr);
        }
    }

    public function getFormAction()
    {
        return $this->getUrl('noncatalogrequesttoquote/index/index/', ['_secure' => true]);
    }

    public function getCustomerId()
    {
        return  $this->session->getCustomerId();
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
