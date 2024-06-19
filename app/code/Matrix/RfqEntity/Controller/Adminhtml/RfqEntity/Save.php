<?php
namespace Matrix\RfqEntity\Controller\Adminhtml\RfqEntity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Save extends Action
{

    /**
    * @var RfqEntityFactory
    */
    protected $rfqentityFactory;

    /**
     * @param  Context           $context
     * @param  RfqEntityFactory $rfqentityFactory
     */
    public function __construct(
        Context $context,
        RfqEntityFactory $rfqentityFactory
    ) {
        $this->rfqentityFactory = $rfqentityFactory;
        parent::__construct($context);
    }

    /**
     * For allow to access or not
     *
     * return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_RfqEntity::rfqentity');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $storeId = (int) $this->getRequest()->getParam('store_id');
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $params = [];
            $rfqentityData = $this->rfqentityFactory->create();
            $rfqentityData->setStoreId($storeId);
            $params['store'] = $storeId;
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            } else {
                $rfqentityData->load($data['entity_id']);
                $params['entity_id'] = $data['entity_id'];
            }
            $rfqentityData->addData($data);

            /*$this->_eventManager->dispatch(
                'mx_rfqentity_rfqentity_prepare_save',
                ['object' => $this->rfqentityFactory, 'request' => $this->getRequest()]
            );*/

            try {
                $rfqentityData->save();
                $this->messageManager->addSuccessMessage(__('You saved the Non-Catalog RFQ Entity.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $params['entity_id'] = $rfqentityData->getId();
                    $params['_current'] = true;
                    return $resultRedirect->setPath('*/*/edit', $params);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Non-Catalog RFQ Entity.'));
            }

            $this->_getSession()->setFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit', $params);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
