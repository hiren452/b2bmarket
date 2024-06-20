<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Buyervisibility;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $_customerRepositoryInterface;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute()
    {
        $postData =$this->getRequest()->getPostValue();

        try {
            $customerId = $postData['buyervisibility']['entity_id'];
            if (isset($postData['buyervisibility']['rfqbuyer_isvisible']) && $customerId>0) {
                $rfqbuyer_isvisible =  $postData['buyervisibility']['rfqbuyer_isvisible'];
                $customer = $this->_customerRepositoryInterface->getById($customerId);
                $customer->setCustomAttribute('rfqbuyer_isvisible', $rfqbuyer_isvisible);
                $this->_customerRepositoryInterface->save($customer);
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->messageManager->addSuccessMessage(__('Buyer visibility successfully updated.'));
                $resultRedirect->setPath('noncatalogrfq/buyervisibility/index');
                return $resultRedirect;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addException($e, __('Something went wrong while editing the customer.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('noncatalogrfq/buyervisibility/index');
            return $resultRedirect;
        }
    }

    protected function _isAllowed()
    {

        return $this->_authorization->isAllowed('Matrix_NoncatalogueRfq::sellervisibility');
    }
}
