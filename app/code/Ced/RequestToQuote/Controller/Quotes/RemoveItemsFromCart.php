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

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\PoFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class RemoveItemsFromCart
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class RemoveItemsFromCart extends Action
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * RemoveItemsFromCart constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param Cart $cart
     * @param PoFactory $poFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Cart $cart,
        PoFactory $poFactory,
        Data $helper,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->poFactory = $poFactory;
        $this->helper = $helper;
        parent::__construct($context, $customerSession, $cart, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        try {
            if (!$this->session->isLoggedIn()) {
                $this->messageManager->addErrorMessage(__('Please login first'));
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }
            $poItemRemoveFlag = false;
            $module_enable = $this->helper->getConfigValue('requesttoquote_configuration/active/enable');
            if ((int)$module_enable) {
                $po = $this->poFactory->create()->load($id);
                if ($po && $po->getId()) {
                    $allQuoteItems = $this->cart->getQuote()->getAllItems();
                    foreach ($allQuoteItems as $item) {
                        if ($item->getCedPoId() == $id) {
                            $this->cart->removeItem($item->getId());

                            $poItemRemoveFlag = true;
                        }
                    }
                    $this->cart->getQuote()->setTotalsCollectedFlag(false);
                    $this->cart->save();
                } else {
                    $this->messageManager->addErrorMessage(__('This Proposal no longer exist.'));
                }
            }
            if ($poItemRemoveFlag) {
                $this->messageManager->addSuccessMessage(__('Proposal Item(s) has been removed successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        $resultRedirect->setPath('requesttoquote/customer/editpo', ['poId' => $id]);
        return $resultRedirect;
    }
}
