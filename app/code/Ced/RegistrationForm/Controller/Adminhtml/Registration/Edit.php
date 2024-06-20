<?php
namespace Ced\RegistrationForm\Controller\Adminhtml\Registration;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
    protected $eavEntity;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity $eavEntity
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->eavEntity = $eavEntity;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = $this->eavEntity->getAttribute('customer', $id);

        if ($id && !$model->getAttributeId()) {
            $this->messageManager->addError(__('This Attribute no longer exists.'));
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('regform_data', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getAttributeId() ? __("Edit Attribute '%1'", $model->getAttributeCode()) : __('New Attribute')
        );

        return $resultPage;
    }
}
