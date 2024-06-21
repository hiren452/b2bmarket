<?php

namespace OX\CustomerMembership\Controller\Adminhtml\CMembership;

use Ced\CustomerMembership\Model\MembershipFactory;
use Ced\CustomerMembership\Model\ResourceModel\Membership;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Action
{
    public $resourceModel;
    public $membershipFactory;

    public $context;

    /**
     * Delete constructor.
     * @param Context $context
     * @param MembershipFactory $membershipFactory
     * @param Membership $resourceModel
     */
    public function __construct(Context $context, MembershipFactory $membershipFactory, Membership $resourceModel)
    {
        $this->context = $context;
        $this->resourceModel = $resourceModel;
        $this->membershipFactory = $membershipFactory;
        parent::__construct($context);
    }

    /**
     * Delete class execute method
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        try {
            $deleteId = $this->getRequest()->getParam('id');
            $model = $this->membershipFactory->create();
            $this->resourceModel->load($model, $deleteId, 'id');
            $this->resourceModel->delete($model);
            $this->messageManager->addSuccessMessage('Membership Deleted successfully');
            return $this->resultRedirectFactory->create()->setPath('cmembership/cmembership/index');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('cmembership/cmembership/index');
        }
    }
}
