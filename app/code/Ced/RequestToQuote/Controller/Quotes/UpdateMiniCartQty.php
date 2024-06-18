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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateMiniCartQty
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class UpdateMiniCartQty extends \Magento\Checkout\Controller\Sidebar\UpdateItemQty
{
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
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * UpdateMiniCartQty constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param CustomerCart $cart
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper,
        CustomerCart $cart
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->cart = $cart;
        parent::__construct($context, $sidebar, $logger, $jsonHelper);
    }

    /**
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $store = $this->storeManager->getStore();
        } catch (\Exception $e) {
            $store = '';
        }
        $module_enable = $this->scopeConfig->getValue('requesttoquote_configuration/active/enable', ScopeInterface::SCOPE_STORE, $store->getId());
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $quoteItem = $this->cart->getQuote()->getItemById($itemId);
        if((int)$module_enable && $quoteItem && $quoteItem->getItemId() && $quoteItem->getCedPoId()) {
            return $this->jsonResponse();
        } else {
            $itemId = (int)$this->getRequest()->getParam('item_id');
            $itemQty = (int)$this->getRequest()->getParam('item_qty');

            try {
                $this->sidebar->checkQuoteItem($itemId);
                $this->sidebar->updateQuoteItem($itemId, $itemQty);
                return $this->jsonResponse();
            } catch (LocalizedException $e) {
                return $this->jsonResponse($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return $this->jsonResponse($e->getMessage());
            }
        }
    }
}
