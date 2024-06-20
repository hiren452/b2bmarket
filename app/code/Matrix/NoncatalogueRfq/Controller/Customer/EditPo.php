<?php
namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

class EditPo extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * EditPo constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param PoFactory $poFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        RfqPoFactory $poFactory,
        Registry $registry,
        array $data = []
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->_poFactory = $poFactory;
        $this->registry = $registry;
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

        if ($poId = $this->getRequest()->getParam('poId')) {
            $currentPo = $this->_poFactory->create()->load($poId);

            if ($currentPo && $currentPo->getId()) {
                $customerId = $currentPo->getPoCustomerId();
                $customer_id = $this->session->getCustomer()->getId();
                if ($customer_id == $customerId) {
                    $this->registry->register('matrix_current_noncatalopo', $currentPo);
                    $resultPage = $this->resultPageFactory->create();
                    return $resultPage;
                }
                $this->messageManager->addErrorMessage(__('This po does not related to you.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/quotes');
            }
            $this->messageManager->addErrorMessage(__('This po no longer exist.'));
            return $this->_redirect('noncatalogrequesttoquote/customer/quotes');
        }
        $this->messageManager->addErrorMessage(__('You are not allowed to update this po. Kindly update your PO only.'));
        return $this->_redirect('customer/account/index');
    }
}
