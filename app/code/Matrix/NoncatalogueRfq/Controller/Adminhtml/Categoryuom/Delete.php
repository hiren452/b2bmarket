<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Categoryuom;

use Matrix\NoncatalogueRfq\Model\CategroyUomFactory;

class Delete extends \Magento\Backend\App\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CategroyUomRepository
     */
    private $categroyUomRepository;

    /**
     * @var CategroyUomFactory
     */
    private $categroyUomFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CategroyUomFactory $categroyUomFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categroyUomFactory = $categroyUomFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model =  $this->categroyUomFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Category UOM.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['advert_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a Category UOM to delete.'));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_NoncatalogueRfq::assignuom');
    }
}
