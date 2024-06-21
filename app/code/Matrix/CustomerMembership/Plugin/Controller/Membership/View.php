<?php

namespace Matrix\CustomerMembership\Plugin\Controller\Membership;

use Magento\Framework\Controller\Result\RedirectFactory;

class View
{
    /**
     * @var \Ced\CsMarketplace\Model\Vendor
     */
    private $vendor;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_custmerSesion;
    /**
     * @var RedirectFactory
     */
    private $redirect;

    public function __construct(
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Customer\Model\Session $session,
        RedirectFactory $redirect
    ) {
        $this->vendor= $vendor;
        $this->_custmerSesion = $session;
        $this->redirect = $redirect;
    }

    public function aroundExecute(
        \Ced\CustomerMembership\Controller\Membership\View $subject,
        callable $proceed
    ) {
        $vendor = $this->vendor->loadByCustomerId($this->_custmerSesion->getCustomerId());
        if ($vendor && $vendor->getId() && $vendor->getStatus() == "approved") {
            $redirect = $this->redirect->create();
            return $redirect->setPath('subscription/membership/index');
        }
        return $proceed();
    }
}
