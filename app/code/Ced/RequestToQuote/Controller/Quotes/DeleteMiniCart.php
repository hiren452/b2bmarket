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
use Magento\Checkout\Model\Sidebar;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteMiniCart
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class DeleteMiniCart extends \Magento\Checkout\Controller\Sidebar\RemoveItem
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * DeleteMiniCart constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Context $context
     * @param RequestInterface $request
     * @param ResultJsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Sidebar $sidebar
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     * @param Validator $validator
     * @param CustomerCart $cart
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Context $context,
        RequestInterface $request,
        ResultJsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Sidebar $sidebar,
        Validator $formKeyValidator,
        LoggerInterface $logger,
        Validator $validator,
        CustomerCart $cart
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->request = $request;
        $this->formKeyValidator = $validator;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->cart = $cart;
        parent::__construct($context, $request, $resultJsonFactory, $resultRedirectFactory, $sidebar, $formKeyValidator, $logger);
    }

    /**
     * @return \Magento\Checkout\Controller\Sidebar\RemoveItem|\Magento\Framework\App\Response\Http|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $store = $this->storeManager->getStore();
        if (!$this->formKeyValidator->validate($this->request) || !$store) {
            return $this->resultRedirectFactory->create()
                ->setPath('*/cart/');
        }

        $error = '';
        $module_enable = $this->scopeConfig->getValue('requesttoquote_configuration/active/enable', ScopeInterface::SCOPE_STORE, $store->getId());
        $cartItemId = (int)$this->getRequest()->getParam('item_id');
        $quoteItem = $this->cart->getQuote()->getItemById($cartItemId);

        if((int)$module_enable && $quoteItem && $quoteItem->getItemId() && $quoteItem->getCedPoId()) {
            $error = __("You can not delete the quote item");
        } else {
            try {
                $this->sidebar->checkQuoteItem($cartItemId);
                $this->sidebar->removeQuoteItem($cartItemId);

            } catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $error = $exception->getMessage();
            } catch (\Zend_Db_Exception $e) {
                $this->logger->critical($e);
                $error = __('An unspecified error occurred. Please contact us for assistance.');
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $error = $exception->getMessage();
            }
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($this->sidebar->getResponseData($error));
        return $resultJson;

    }
}
