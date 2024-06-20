<?php
namespace Ced\RegistrationForm\Controller\Adminhtml\Registration;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    protected $attributeFactory;
    protected $eavEntity;

    public function __construct(
        Action\Context $context,
        \Ced\RegistrationForm\Model\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity $eavEntity
    ) {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->eavEntity = $eavEntity;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        if (!is_array($id)) {
            $ids[] = $id;
        } else {
            $ids = $id;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        foreach ($ids as $id) {
            try {
                $eavModel = $this->eavEntity->getAttribute('customer', $id);
                if ($eavModel && $eavModel->getId()) {
                    $eavModel->delete();
                }

                $model = $this->attributeFactory->create()->load($id);
                if ($model && $model->getId()) {
                    $model->delete();
                }

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting the customer attribute.'));
            }
        }
        $this->messageManager->addSuccess(__('Attribute deleted Successfully'));
        return $resultRedirect->setPath('*/*/attribute');
    }
}
