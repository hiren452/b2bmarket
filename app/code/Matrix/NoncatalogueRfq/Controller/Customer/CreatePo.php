<?php
namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as rfqProductCollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqNegotiationFactory;
use Matrix\NoncatalogueRfq\Model\RfqPo;
use Matrix\NoncatalogueRfq\Model\RfqVendor;

class CreatePo extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    protected $_po;

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
     * @var rfqnegotiationFactory
     */
    protected $rfqnegotiationFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    private $poPrefixHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        RfqPo $po,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        RfqVendor $rfqVendor,
        rfqProductCollectionFactory    $rfqProductCollectionFactory,
        RfqNegotiationFactory $rfqnegotiationFactory,
        \MageMonkeys\PoPrefix\Helper\Data $poPrefixHelper,
        array $data = []
    ) {

        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->rfqProductCollectionFactory = $rfqProductCollectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->rfqVendor = $rfqVendor;
        $this->_po =  $po;
        $this->rfqnegotiationFactory = $rfqnegotiationFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->poPrefixHelper = $poPrefixHelper;
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

        $postdata = $this->getRequest()->getParams();

        //$this->rfqPoFactor

        try {
            $quote = $this->quoteFactory->create()->load($this->getRequest()->getParam('quoteId'));

            $customer_id = $this->session->getCustomer()->getId();

            /*if ($quote && $quote->getId()) {
               $quote_id =  $quote->getId() ;
               $customerId = $quote->getCustomerId();
              if ($customerId != $customer_id) {
                  $this->messageManager->addErrorMessage(__('This quote does not belongs to you.'));
                  return $this->_redirect ('nnoncatalogrequesttoquote/customer/editquote/',['quoteId' => $this->getRequest()->getParam('quoteId')]);
              }
         } else {
              $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
              return $this->_redirect ('nnoncatalogrequesttoquote/customer/editquote/',['quoteId' => $this->getRequest()->getParam('quoteId')]);
         }  */

            $rfqnegotiationModel = $this->rfqnegotiationFactory->create()->load($this->getRequest()->getParam('accepected_negotiate_id'));

            $pocollection = $this->_po->getCollection();
            if (sizeof($pocollection) > 0) {
                $po_id =  $pocollection->getLastItem()->getPoId();
                $po_id++;
                $poincId = $this->poPrefixHelper->getPoPrefix('non_cat_rfq_create') . '0' . $po_id;
            } else {
                $poincId = $this->poPrefixHelper->getPoPrefix('non_cat_rfq_create') . '01';
            }
            $quote_id = $this->getRequest()->getParam('quoteId');
            $qty = $rfqnegotiationModel->getData('quoted_qty');
            $price = $rfqnegotiationModel->getData('quoted_price');
            $po_qty = $rfqnegotiationModel->getData('negotiotion_qty');
            $po_price = $rfqnegotiationModel->getData('negotiotion_price');
            $remaining_price =
            $customer_id = $rfqnegotiationModel->getData('customer_id');
            $vendor_id = $rfqnegotiationModel->getData('vendor_id');

            $remaining_price = ($price * $qty) - ($po_price * $po_qty);

            $this->_po->setData('rfq_id', $quote_id);
            $this->_po->setData('po_increment_id', $poincId);
            $this->_po->setData('quote_approved_qty', $qty);
            $this->_po->setData('quote_approved_price', $price);
            $this->_po->setData('po_qty', $po_qty);
            $this->_po->setData('po_price', $po_price);
            $this->_po->setData('remaining_price', $remaining_price);
            $this->_po->setData('po_customer_id', $customer_id);
            $this->_po->setData('vendor_id', $vendor_id);
            $this->_po->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
            $this->_po->save();

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
        }
        $this->messageManager->addSuccessMessage(__('You have successfully replyed to your Negotion.'));
        return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
    }
}
