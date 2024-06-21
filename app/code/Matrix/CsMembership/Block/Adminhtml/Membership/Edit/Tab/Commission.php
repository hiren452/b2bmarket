<?php

namespace Matrix\CsMembership\Block\Adminhtml\Membership\Edit\Tab;

use Ced\CsMembership\Model\MembershipFactory;
use Magento\Backend\Block\Widget\Container;

class Commission extends Container
{
    protected $membershipFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        MembershipFactory $membershipFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->membershipFactory = $membershipFactory;

        $this->setTemplate('csmembership/commission.phtml');
    }

    public function getCommission()
    {
        if ($this->getRequest()->getParam('id')) {
            return $this->membershipFactory->create()->load($this->getRequest()->getParam('id'))->getData('commission');
        }
        return false;
    }
}
