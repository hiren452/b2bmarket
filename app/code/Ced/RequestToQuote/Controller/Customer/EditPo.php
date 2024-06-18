<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Controller\Customer;

use Ced\RequestToQuote\Model\PoFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class EditPo extends Action
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
        PoFactory $poFactory,
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        if ($poId = $this->getRequest()->getParam('poId')) {
            $resultRedirect->setPath('requesttoquote/customer/po');
            $currentPo = $this->_poFactory->create()->load($poId);
            if ($currentPo && $currentPo->getId()) {
                $customerId = $currentPo->getPoCustomerId();
                $customer_id = $this->session->getCustomer()->getId();
                if ($customer_id == $customerId) {
                    $this->registry->register('current_po', $currentPo);
                    $resultPage = $this->resultPageFactory->create();
                    $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
                    if ($navigationBlock) {
                        $navigationBlock->setActive('requesttoquote/customer/po');
                    }
                    return $resultPage;
                }
                $this->messageManager->addErrorMessage(__('This po does not related to you.'));
                return $resultRedirect;
            }
            $this->messageManager->addErrorMessage(__('This po no longer exist.'));
            return $resultRedirect;
        }
        $this->messageManager->addErrorMessage(__('You are not allowed to update this po. Kindly update your PO only.'));
        $resultRedirect->setPath('customer/account/index');
        return $resultRedirect;
    }
}
