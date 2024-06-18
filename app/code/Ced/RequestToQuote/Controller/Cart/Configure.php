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
namespace Ced\RequestToQuote\Controller\Cart;

use Magento\Framework;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Configure
 * @package Ced\RequestToQuote\Controller\Cart
 */
class Configure extends \Magento\Checkout\Controller\Cart\Configure
{
    /**
     * @return Framework\Controller\Result\Redirect|Framework\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }

        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addError(__("We can't find the quote item."));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('checkout/cart');
            }
            $module_enable = $this->_scopeConfig->getValue('requesttoquote_configuration/active/enable');
            if ((int)$module_enable && $quoteItem->getCedPoId() && $quoteItem->getProduct()->getId() == $productId) {
                $existPoId = $quoteItem->getCedPoId();
                $link = '<a href="' . $this->_url->getUrl('requesttoquote/customer/editpo', ['poId' => $existPoId]) . '">' . __('Click Here') . '</a>';
                $this->messageManager->addError(__('You can not update proposal item(s) from cart. ' . $link . ' to remove Proposal Item(s) from cart.'));
                return $this->_goBack();
            }
            return parent::execute();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot configure the product.'));
            $this->messageManager->addError(__($e));
            return $this->_goBack();
        }
    }
}
