<?php

namespace Matrix\CustomerMembership\Plugin\Controller\Membership;

use Magento\Framework\Controller\Result\RedirectFactory;

class NewPlan
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_custmerSesion;
    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    private $vendor;
    /**
     * @var RedirectFactory
     */
    private $redirect;

    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        RedirectFactory $redirect
    ) {
        $this->_custmerSesion = $session;
        $this->vendor         = $vendor;
        $this->redirect = $redirect;
    }

    public function aroundExecute(
        \Ced\CustomerMembership\Controller\Membership\NewPlan $subject,
        callable $proceed
    ) {
        if ($this->_custmerSesion->isLoggedIn()) {
            $redirect = $this->redirect->create();
            $vendor = $this->vendor->loadByCustomerId($this->_custmerSesion->getCustomerId());

            if ($vendor && $vendor->getId() && $vendor->getStatus()=="approved") {
                return $redirect->setPath('subscription/membership/index');
            }
        }
        return $proceed();
    }
}
