<?php

namespace Ced\CustomerMembership\Controller\Adminhtml\Cmembership;

use Ced\CustomerMembership\Model\MembershipFactory;
use Magento\Backend\Model\Session;

class Save extends \Magento\Backend\App\Action
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        MembershipFactory $membershipFactory,
        Session $backendSession
    ) {
        parent::__construct($context);
        $this->membershipFactory = $membershipFactory;
        $this->_backendSession = $backendSession;
    }

    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        /*$data['customergroups']=implode(',',$data['customergroups']);*/
        if ($data) {

            $model = $this->membershipFactory->create();
            if ($this->getRequest()->getParam('id')) {
                $model->load($this->getRequest()->getParam('id'));

            }

            //$model->addData($data);
            try {
                //$model->save();
                $editid=$this->getRequest()->getParam('id');
                if ($editid) {
                    $mode=\Ced\CustomerMembership\Model\Membership::PRODUCT_EDIT;
                } else {
                    $mode=\Ced\CustomerMembership\Model\Membership::PRODUCT_NEW;
                }
                $result = new \Magento\Framework\DataObject();
                $this->_eventManager->dispatch('ced_customermembership_productcreate', [ 'mode' =>$mode,'postdata'=>$this->getRequest()->getPostValue(),'editid'=>$editid,'result'=>$result]);
                if ($result->getResult()) {
                    $data['product_id'] = $result->getResult();
                }
                $model->addData($data);
                $model->save();
                $this->messageManager->addSuccess(__('Membership Plan Has Been Saved'));
                $this->_backendSession->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the membership plan.'));
            }

            $this->_getSession()->setFormData($this->getRequest()->getPostValue());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        } else {
            $this->messageManager->addError(__('No Data To Save'));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
