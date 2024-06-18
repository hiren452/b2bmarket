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

class EditQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\RequestToQuote\Model\Quote $quote
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\RequestToQuote\Model\Quote $quote,
        array $data = []
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_getSession = $customerSession;
        $this->_quote = $quote;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (! $this->_getSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $quote_id = $this->getRequest()->getParam('quoteId');
        $customer_id = $this->_getSession->getCustomer()->getId();
        $customerId = $this->_quote->load($quote_id)->getCustomerId();
        if($customer_id == $customerId) {
            $resultPage = $this->resultPageFactory->create();
            $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('requesttoquote/customer/quotes');
            }
            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update the available quotes only.'));
            $resultRedirect->setPath('customer/account/index');
            return $resultRedirect;
        }
    }
}
