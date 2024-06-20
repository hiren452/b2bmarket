<?php

namespace B2bmarkets\MultiStepReg\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;

    protected $jsonResultFactory;

    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->pageFactory           = $pageFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->customerSession   = $customerSession;

        return parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
        }

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Multi Step Registration'));

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
