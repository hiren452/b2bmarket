<?php

namespace Matrix\CustomerMembership\Plugin\Controller\Adminhtml\Cmembership;

use Ced\CustomerMembership\Model\MembershipFactory;
use Ced\CustomerMembership\Model\ResourceModel\Membership;
use Ced\CustomerMembership\Model\ResourceModel\Membership\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete
{
    private $filter;

    private $membershipModel;

    private $membershipResource;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Filter $filter,
        MembershipFactory $membershipModel,
        Membership $membershipResource,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->filter = $filter;
        $this->membershipModel = $membershipModel;
        $this->collectionFactory = $collectionFactory;
        $this->membershipResource = $membershipResource;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function aroundExecute(
        \Ced\CustomerMembership\Controller\Adminhtml\Cmembership\MassDelete $subject,
        callable $proceed
    ) {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $poIds = $collection->getAllIds();
        $poupdated = 0;
        foreach ($poIds as $key => $value) {
            $modelFactory = $this->membershipModel->create();
            $this->membershipResource->load($modelFactory, $value, 'id');
            if ($modelFactory->getPlanName() == 'seller_free_membership') {
                continue;
            }
            $this->membershipResource->delete($modelFactory);
            $poupdated++;
        }
        $this->messageManager->addSuccessMessage(__($poupdated . ' Membership Deleted successsfully.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
