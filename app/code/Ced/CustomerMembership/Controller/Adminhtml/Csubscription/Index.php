<?php

namespace Ced\CustomerMembership\Controller\Adminhtml\Csubscription;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Customer Subscription'), __('Customer Subscription'));
        $resultPage->addBreadcrumb(__('Customer Subscription'), __('Customer Subscription'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Subscription'));

        return $resultPage;
    }
}
