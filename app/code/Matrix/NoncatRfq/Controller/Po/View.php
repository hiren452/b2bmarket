<?php

namespace Matrix\NoncatRfq\Controller\Po;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNegotiation\CollectionFactory as RfqNegotiationCollectionFactory;

class View extends \Ced\CsMarketplace\Controller\Vendor
{
    protected $rfqNegotiationCollectionFactory;
    protected $session;
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        RfqNegotiationCollectionFactory $rfqNegotiationCollectionFactory
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->rfqNegotiationCollectionFactory = $rfqNegotiationCollectionFactory;
        $this->session = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        if ($id = $this->getRequest()->getParam('id')) {
            $currentQuote = $this->quoteFactory->create()->load($id);
            $negotistionObj =  $this->getNegotiationAccpectedId();

            if ($currentQuote && $currentQuote->getRfqId()) {
                $this->_coreRegistry->register('matrix_current_rfq', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Create Proposal for # %1', $currentQuote->getQuoteIncrementId()));
                return $resultPage;
            }
        }
        $this->messageManager->addErrorMessage(__('Something went wrong.'));
        return $this->_redirect('vendornoncatrfq/rfq/view/', ['id' => $this->getRequest()->getParam('id')]);
    }

    private function getVendorId()
    {
        return $this->session->getVendorId();
    }

    public function getNegotiationAccpectedId()
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $this->getRequest()->getParam('id'))
        ->addFieldToFilter('vendor_id', $vendor_id)
        ->addFieldToFilter('is_accpected', ['eq'=>1]);
        /// echo $collection->getSelect();
        return $collection->getFirstItem();

    }
}
