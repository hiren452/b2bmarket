<?php

namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart as ModelCart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Quotes extends Cart
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession
     */
    protected $_customersession;

    /**
     * Quotes constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param ModelCart $cart
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customersession
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        ModelCart $cart,
        PageFactory $resultPageFactory,
        CustomerSession $customersession
    ) {

        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );

        $this->_customersession = $customersession;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|
     *         \Magento\Framework\Controller\ResultInterface|
     *         \Magento\Framework\View\Result\Page|
     *         void
     */
    public function execute()
    {

        if (! $this->_customersession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $this->_redirect('customer/account/login');
            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Non-catalog Quotes'));
        return $resultPage;
    }
}
