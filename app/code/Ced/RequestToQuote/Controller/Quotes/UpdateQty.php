<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Controller\Quotes;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateQty
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class UpdateQty extends \Magento\Checkout\Controller\Cart\UpdatePost
{
    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var CustomerSession
     */
    protected $_session;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpdateQty constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerSession $customerSession
     * @param CustomerCart $cart
     * @param ResolverInterface $localeResolver
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerSession $customerSession,
        CustomerCart $cart,
        ResolverInterface $localeResolver,
        Escaper $escaper,
        LoggerInterface $logger
    ) {

        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        $this->_session = $customerSession;
        $this->localeResolver = $localeResolver;
        $this->escaper = $escaper;
        $this->logger = $logger;
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * Update shopping cart data action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $module_enable = $this->_scopeConfig->getValue('requesttoquote_configuration/active/enable');
        $poItemExistFlag = false;
        $existPoId = '';
        if ((int)$module_enable) {
            $allItems = $this->_checkoutSession->getQuote()->getAllItems();
            foreach ($allItems as $item) {
                if ($item->getCedPoId()) {
                    $existPoId = $item->getCedPoId();
                    $poItemExistFlag = true;
                    break;
                }
            }
        }
        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');
        switch ($updateAction) {
            case 'empty_cart':
                if($poItemExistFlag) {
                    if ($existPoId) {
                        $link = '<a href="' . $this->_url->getUrl('requesttoquote/customer/editpo', ['poId' => $existPoId]) . '">' . __('Click Here') . '</a>';
                        $this->messageManager->addError(__('You can not remove proposal item(s) from cart. ' . $link . ' to remove Proposal Item(s) from cart.'));
                    } else {
                        $this->messageManager->addError(__('You can not remove proposal item(s) from cart.'));
                    }
                    return $this->_goBack();
                }
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                if($poItemExistFlag) {
                    if ($existPoId) {
                        $link = '<a href="' . $this->_url->getUrl('requesttoquote/customer/editpo', ['poId' => $existPoId]) . '">' . __('Click Here') . '</a>';
                        $this->messageManager->addError(__('Quantity edit for the proposal item(s) is not allowed. ' . $link . ' to remove Proposal Item(s) from cart.'));
                    } else {
                        $this->messageManager->addError(__('Quantity edit for the proposal item(s) is not allowed.'));
                    }
                    return $this->_goBack();
                }
                $this->_updateShoppingCart();
                break;
            default:
                if($poItemExistFlag) {
                    if ($existPoId) {
                        $proposalLink = '<a href="' . $this->_url->getUrl('requesttoquote/customer/editpo', ['poId' => $existPoId]) . '">' . __('Click Here') . '</a>';
                        $this->messageManager->addError(__('Quantity edit for the proposal item(s) is not allowed. ' . $proposalLink . ' to remove Proposal Item(s) from cart.'));
                    } else {
                        $this->messageManager->addError(__('Quantity edit for the proposal item(s) is not allowed.'));
                    }
                    return $this->_goBack();
                }
                $this->_updateShoppingCart();
        }
        return $this->_goBack();
    }
}
