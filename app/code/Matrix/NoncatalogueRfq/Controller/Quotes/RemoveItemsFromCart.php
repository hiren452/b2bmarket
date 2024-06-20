<?php

namespace Matrix\NoncatalogueRfq\Controller\Quotes;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

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
        RfqPoFactory $poFactory,
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
        $id = $this->getRequest()->getParam('id');

        try {
            if (!$this->session->isLoggedIn()) {
                $this->messageManager->addErrorMessage(__('Please login first'));
                return $this->_redirect('customer/account/login');
            }
            $poItemRemoveFlag = false;
            $module_enable = $this->helper->getConfigValue('requesttoquote_configuration/active/enable');
            if ((int)$module_enable) {
                $po = $this->poFactory->create()->load($id);

                if ($po && $po->getId()) {

                    $allQuoteItems = $this->cart->getQuote()->getAllItems();
                    foreach ($allQuoteItems as $item) {
                        if ($item->getMatrixPoId() == $id) {
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
        return $this->_redirect('noncatalogrequesttoquote/customer/editpo', ['poId' => $id]);
    }
}
