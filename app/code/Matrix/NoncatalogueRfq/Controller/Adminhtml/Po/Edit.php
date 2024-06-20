<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Po;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

class Edit extends \Magento\Backend\App\Action
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
     * @var poFactory
     */
    protected $poFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        RfqPoFactory $quoteFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->poFactory= $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $currentPo = $this->poFactory->create()->load($id);
            if ($currentPo && $currentPo->getId()) {
                $this->_coreRegistry->register('matrix_current_po', $currentPo);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Non-Catalog PO # %1', $currentPo->getPoIncrementId()));
                return $resultPage;
            }
            $this->messageManager->addErrorMessage(__('This PO no longer exist.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('noncatalogrfq/po/index');
    }
}
