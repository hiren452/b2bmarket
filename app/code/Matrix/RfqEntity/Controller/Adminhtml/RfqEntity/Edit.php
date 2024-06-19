<?php

namespace Matrix\RfqEntity\Controller\Adminhtml\RfqEntity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Edit extends Action
{

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
    * @var RfqEntityFactory
    */
    protected $rfqentityFactory;

    /**
     * @param  Context           $context
     * @param  PageFactory       $resultPageFactory
     * @param  Registry          $registry
     * @param  RfqEntityFactory $rfqentityFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        RfqEntityFactory $rfqentityFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->rfqentityFactory = $rfqentityFactory;
        parent::__construct($context);
    }

    /**
     * For allow to access or not
     *
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_RfqEntity::rfqentity');
    }

    /**
     * Edit
     *
     * @return \Magento\Backend\Model\View\Result\Page | \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $rfqentityData = $this->rfqentityFactory->create();

        if ($id) {
            $rfqentityData->load($id);
            if (!$rfqentityData->getId()) {
                $this->messageManager->addErrorMessage(__('Non-Catalog RFQ Entity record no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $rfqentityData->addData($data);
        }

        $this->_coreRegistry->register('entity_id', $id);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Matrix_RfqEntity::manage_grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Non-Catalog RFQ Entity'));

        return $resultPage;
    }
}
