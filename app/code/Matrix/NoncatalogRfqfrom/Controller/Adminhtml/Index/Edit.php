<?php
namespace Matrix\NoncatalogRfqfrom\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;
    protected $resultPageFactory;
    protected $attribute;
    protected $backendSession;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Attribute $attribute,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->attribute = $attribute;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');

        if ($id) {
            $model = $this->attribute->load($id);

            if (!$model->getAttributeId()) {
                $this->messageManager->addError(__('This Attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('noncatalogrfqform_data', $model);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Matrix_NoncatalogRfqfrom::manage_view');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getAttributeId() ? __("Edit Attribute '%1' ", $model->getAttributeCode()) : __('New Attribute'));

        return $resultPage;
    }
}
