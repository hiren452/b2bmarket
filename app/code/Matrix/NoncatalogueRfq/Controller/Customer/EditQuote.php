<?php
namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Magento\Framework\Registry;
use Matrix\NoncatalogueRfq\Helper\Data as Helper;

class EditQuote extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Matrix\NoncatalogueRfq\Model\NoncatalogRfq  $quote,
        Registry $registry,
        Helper $helper,
        array $data = []
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->_getSession = $customerSession;
        $this->_quote = $quote;
        $this->_coreRegistry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function execute()
    {
        if (! $this->_getSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $this->_redirect('customer/account/login');
            return;
        }

        if (!$this->helper->isSubscribedMembership()) {
            $this->_redirect('membership/membership/view/');
            return;
        }

        $quote_id = $this->getRequest()->getParam('quoteId');
        $customer_id = $this->_getSession->getCustomer()->getId();
        $currentRfq = $this->_quote->load($quote_id);
        if (!isset($currentRfq) && $currentRfq->getRfqId()<=0) {
            $this->messageManager->addErrorMessage(__('The Non-catalog RFQ not exist.'));
            return $this->_redirect('customer/account/index');
        }
        $customerId = $currentRfq->getCustomerId();
        if ($customer_id == $customerId) {
            $quoteIncrementId =  $currentRfq->getData('quote_increment_id');
            $this->_coreRegistry->register('current_noncatquote', $currentRfq);
            $resultPage = $this->resultPageFactory->create();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('noncatalogrequesttoquote/customer/quotes');
            }
            $resultPage->getConfig()->getTitle()->set(__('Non-catalog Quote # ') . $quoteIncrementId);
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update the available quotes only.'));
            return $this->_redirect('customer/account/index');
        }
    }
}
