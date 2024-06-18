<?php
namespace Matrix\NoncatalogRfqfrom\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Attribute extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Attribute constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Matrix_NoncatalogRfqfrom::manage_gird');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Non-Catalog RFQ Form [ Additional Fields ]'));

        return $resultPage;
    }
}
