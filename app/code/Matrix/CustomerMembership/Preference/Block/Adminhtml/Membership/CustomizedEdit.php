<?php

namespace Matrix\CustomerMembership\Preference\Block\Adminhtml\Membership;

use Ced\CustomerMembership\Block\Adminhtml\Membership\Edit;

class CustomizedEdit extends Edit
{
    private $membershipModel;
    private $membershipResource;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Ced\CustomerMembership\Model\MembershipFactory $membershipModel,
        \Ced\CustomerMembership\Model\ResourceModel\Membership $membershipResource,
        array $data = []
    ) {
        $this->membershipModel = $membershipModel;
        $this->membershipResource = $membershipResource;

        parent::__construct(
            $context,
            $registry,
            $data
        );
        $membershipFactory  = $this->membershipModel->create();
        $this->membershipResource->load($membershipFactory, $this->getRequest()->getParam('id'), 'id');
        if ($membershipFactory->getData('plan_name') == \Ced\CustomWork\Observer\Assignmembership::seller_free_membership) {
            $this->removeButton('delete');
            //            $this->removeButton('save_and_edit_button');
            //            $this->removeButton('save');
        }
    }
}
