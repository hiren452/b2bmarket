<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Customermembership\Controller\Membership;

use Magento\Catalog\Model\ProductFactory as productFactory;

class Subscribe extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $cart;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        productFactory $productFactory
    ) {
        $this->cart = $cart;
        $this->_customerSession = $customerSession;
        $this->urlModel = $urlFactory;
        $this->product = $product;
        $this->_checkoutSession = $checkoutSession;
        $this->formKey=$formkey;
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    public function execute()
    {

        if (!$this->_customerSession->isLoggedIn()) {

            $productobj = $this->productFactory->create()->load($this->getRequest()->getParam('product_id'));
            if (!$productobj->getId()) {
                $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
                $this->_redirect('*/*/view');
                return;
            }

            if ($this->getRequest()->getParam('price')==0) {
                $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
                $this->_customerSession->setBeforeAuthUrl($this->urlModel->create()->getUrl('membership/membership/confirm', ['_secure' => true, '_current' => true]));
                $this->_redirect('customer/account/create');

                return;
            }

            try {
                $params = ['product' => $this->getRequest()->getParam('product_id'), 'qty' => '1'];
                $this->cart->truncate();
                $this->cart->addProduct($productobj, $params);
                $this->cart->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something Went Wrong'));
                $this->_redirect('*/*/view');
                return;
            }

            $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
            $this->_customerSession->setBeforeAuthUrl($this->urlModel->create()->getUrl('checkout/index/index', ['_secure' => true, '_current' => true]));
            $this->_redirect('customer/account/create');
            return;
        }
        $membershipsubscription = $this->collectionFactory->getCollection()->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId())->addFieldToFilter('status', 'running');
        if (count($membershipsubscription->getData())>0) {
            $this->messageManager->addError(__('You Cannot Subscribed For New Membership as you have alreday running membership plan'));
            $this->_redirect('*/*/view');
            return;
        }

        /*
        $params = array(
                'product' => $this->getRequest()->getParam('productid'), // This would be $product->getId()
                'qty' => 1,
                'price' => $this->getRequest()->getParam('price'),

        ); */

        $productobj = $this->productFactory->create()->load($this->getRequest()->getParam('product_id'));
        if (!$productobj->getId()) {
            $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
            $this->_redirect('*/*/view');
            return;
        }

        if ($this->getRequest()->getParam('price')==0) {
            $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));

            $this->_redirect('membership/membership/confirm');

            return;
        }

        try {
            $params = [
                'form_key' => $this->formKey->getFormKey(),
                'product' => $this->getRequest()->getParam('product_id'),
                'qty' => 1,
                'price' => $this->getRequest()->getParam('price'),
            ];
            $_product = $this->product->load($this->getRequest()->getParam('product_id'));
            $this->cart->truncate();
            $this->cart->addProduct($_product, $params);
            $this->cart->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something Went Wrong'));
            $this->_redirect('*/*/view');
            return;
        }
        $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
        $this->_redirect('checkout');
        return;
    }
}
