<?php

namespace Ced\CustomerMembership\Controller\Adminhtml\Cmembership;

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
        $resultPage->addBreadcrumb(__('Customer Membership'), __('Customer Membership'));
        $resultPage->addBreadcrumb(__('Customer Membership'), __('Customer Membership'));
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Membership'));

        return $resultPage;
    }
}
