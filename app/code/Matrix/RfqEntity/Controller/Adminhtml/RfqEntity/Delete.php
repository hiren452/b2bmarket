<?php
namespace Matrix\RfqEntity\Controller\Adminhtml\RfqEntity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Delete extends Action
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
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_RfqEntity::rfqentity');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);

        try {
            $rfqentityData = $this->rfqentityFactory->create()->load($id);
            if ($rfqentityData->getId()) {
                $rfqentityData->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Non-Catalog RFQ Entity.'));
            } else {
                $this->messageManager->addErrorMessage(__('Non-Catalog RFQ Entity does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath('*/*');
    }
}
