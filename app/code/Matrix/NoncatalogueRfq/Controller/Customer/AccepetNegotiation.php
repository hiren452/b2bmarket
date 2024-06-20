<?php
namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNegotiation\CollectionFactory as RfqNegotiationCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as rfqProductCollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqNegotiationFactory;
use Matrix\NoncatalogueRfq\Model\RfqVendor;

class AccepetNegotiation extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var RfqVendor|RfqVendor
     */
    protected $rfqVendor;

    /**
     * @var CollectionFactory
     */
    protected $rfqProductCollectionFactory;

    protected $session;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var rfqnegotiationFactory
     */
    protected $rfqnegotiationFactory;

    protected $rfqNegotiationCollectionFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        RfqVendor $rfqVendor,
        rfqProductCollectionFactory    $rfqProductCollectionFactory,
        RfqNegotiationFactory $rfqnegotiationFactory,
        RfqNegotiationCollectionFactory $rfqNegotiationCollectionFactory,
        Data $helper,
        array $data = []
    ) {

        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->rfqProductCollectionFactory = $rfqProductCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helper = $helper;
        $this->rfqVendor = $rfqVendor;
        $this->rfqnegotiationFactory = $rfqnegotiationFactory;
        $this->rfqNegotiationCollectionFactory = $rfqNegotiationCollectionFactory;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context, $data);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute()
    {

        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $this->_redirect('customer/account/login');
            return;
        }

        /*if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid access.Please try again.'));
            return $this->_redirect ('vendornoncatrfq/rfq/index/');
        }*/

        if ($this->getRequest()->getParam('quoteId')==null) {
            $this->messageManager->addErrorMessage(__('Quote Not Found'));
            $this->_redirect('noncatalogrequesttoquote/customer/quotes/');
            return;
        }

        $quoteId =  $this->getRequest()->getParam('quoteId');
        if ($this->isNegotiationAccpected($quoteId)) {
            $this->messageManager->addErrorMessage(__('Not allowed to add Negotiation as Negotiation already accepted.'));
            return $this->_redirect('noncatalogrequesttoquote/customer/editquote/', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
        }

        $postdata = $this->getRequest()->getParams();

        try {
            $quote = $this->quoteFactory->create()->load($this->getRequest()->getParam('quoteId'));
            $customer_id = $this->session->getCustomer()->getId();
            /*$RfqVedor =$this->rfqVendor->getCollection()
               ->addFieldtoFilter('rfq_id', $this->getRequest()->getParam('quoteId'))
               ->addFieldtoFilter('vendor_id', $vendor_id)->getFirstItem();*/
            if ($quote && $quote->getId()) {
                $quote_id =  $quote->getId();
                $customerId = $quote->getCustomerId();
                if ($customerId != $customer_id) {
                    $this->messageManager->addErrorMessage(__('This quote does not belongs to you.'));
                    return $this->_redirect('noncatalogrequesttoquote/customer/editquote/', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
                }
            } else {
                $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/editquote/', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
            }
            $negotiate_id = $postdata['accepected_negotiate_id'];
            $rfqnegotiationModel = $this->rfqnegotiationFactory->create()->load($negotiate_id);
            if ($rfqnegotiationModel->getData('is_accpected')==1) {
                $this->messageManager->addErrorMessage(__('Negotion already accepected. Please wait for Seller\'s Proposal'));
                return $this->_redirect('noncatalogrequesttoquote/customer/editquote/', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
            }
            $rfqnegotiationModel->setData('is_accpected', 1);
            $rfqnegotiationModel->save();

            $quote->setData('status', \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
            $quote->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
        }
        $this->messageManager->addSuccessMessage(__('You have successfully accepected Vendors Negotion.'));
        return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
    }

    public function isNegotiationAccpected($id)
    {
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $id)
        ->addFieldToFilter('is_accpected', ['eq'=>1]);
        $isAccpected =  ($collection->getSize()) ? true : false;
        return $isAccpected;
    }
}
