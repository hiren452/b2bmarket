<?php

namespace Matrix\CustomerMembership\Controller\Membership;

class AlaCart extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $_custmerSesion;

    protected $vendor;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\CsMarketplace\Model\Vendor $vendor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_custmerSesion = $session;
        $this->vendor          = $vendor;

        parent::__construct($context);
    }
    public function execute()
    {
        if (!$this->_custmerSesion->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }
        //=== check seller or not
        $vendor = $this->vendor->loadByCustomerId($this->_custmerSesion->getCustomerId());
        if ($vendor && $vendor->getId()) {
            $this->_redirect('csmembership/membership/alacart');
            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Ala Cart'));
        return $resultPage;
    }
}
