<?php
namespace Matrix\NoncatRfq\Controller\Rfq;

use Ced\RequestToQuote\Model\QuoteFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\Message;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\RfqVendor;

class Savequotes extends \Ced\CsMarketplace\Controller\Vendor
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
    * @var Data
    */
    protected $helper;

    /**
    * @var Message
    */
    protected $message;

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
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        RfqVendor $rfqVendor,
        Message $message,
        Data $helper
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->rfqVendor = $rfqVendor;
        $this->helper = $helper;
        $this->message = $message;
        $this->_formKeyValidator = $formKeyValidator;
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

        if (!$this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('Soething wen\'t wrong please try again.'));
            return $this->_redirect('vendornoncatrfq/rfq/index/');
        }

        $quote = $this->quoteFactory->create()->load($this->getRequest()->getParam('id'));
        $customer_id = $this->session->getCustomer()->getId();
        $vendor_id =  $this->getVendorId();
        $RfqVedor =$this->rfqVendor->getCollection()
            ->addFieldtoFilter('rfq_id', $this->getRequest()->getParam('id'))
            ->addFieldtoFilter('vendor_id', $vendor_id)->getFirstItem();

        if ($quote && $quote->getId()) {
            if ($vendor_id != $RfqVedor->getVendorId() && $quote->getRfqType()==$this->helper::NONCATALOG_RFQ_TYPE_PRIVATE) {
                $this->messageManager->addErrorMessage(__('This quote does not belongs to you.'));
                return $this->_redirect('vendornoncatrfq/rfq/index');
            }
        } else {
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
            return $this->_redirect('vendornoncatrfq/rfq/index');
        }

        $postdata = $this->getRequest()->getParams();

        if($vendor_id == $RfqVedor->getVendorId() || $quote->getRfqType()==$this->helper::NONCATALOG_RFQ_TYPE_PUBLIC) {
            if ($this->getRequest()->getParam('send') && $postdata['message']) {
                $this->message->setData('customer_id', $quote->getCustomerId());
                $this->message->setData('quote_id', $postdata['id']);
                $this->message->setData('vendor_id', $vendor_id);
                $this->message->setData('message', $postdata['message']);
                $this->message->setData('sent_by', 'Vendor');
                $this->message->save();
            }
        } else {
            $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update your quotes only.'));
            return $this->_redirect('customer/account/index');
        }

        /*if ($id = $this->getRequest()->getParam('id')) {
            $currentQuote = $this->quoteFactory->create()->load($id);
            $postdata = $this->getRequest()->getParams();

            if ($currentQuote && $currentQuote->getId()) {
                $this->_coreRegistry->register('current_noncatquote', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Quote # %1', $currentQuote->getQuoteIncrementId()));
                return $resultPage;
            }
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }*/
        return $this->_redirect('vendornoncatrfq/rfq/index');
    }

    private function getVendorId()
    {
        return $this->session->getVendorId();
    }

}
