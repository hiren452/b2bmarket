<?php

namespace Matrix\CustomerMembership\Preference;

use Ced\CustomerMembership\Controller\Membership\Subscribe;
use Ced\CustomerMembership\Helper\Data;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class CustomisedSubscribe extends Subscribe
{
    protected $helper;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        Session $customerSession,
        UrlFactory $urlFactory,
        Cart $cart,
        Product $product,
        FormKey $formkey,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->product = $product;
        parent::__construct(
            $context,
            $checkoutSession,
            $customerSession,
            $urlFactory,
            $cart,
            $product,
            $formkey,
            $resultPageFactory
        );
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
            $productobj = $$this->product->load($this->getRequest()->getParam('product_id'));
            if (!$productobj->getId()) {
                $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
                $this->_redirect('*/*/view');
                return;
            }

            /*if($this->getRequest()->getParam('price')==0){
                $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
                $this->_customerSession->setBeforeAuthUrl($this->urlModel->create()->getUrl('membership/membership/confirm', ['_secure' => true, '_current' => true]));
                $this->_redirect('customer/account/create');

                return;
            }*/

            try {
                $params = ['product' => $this->getRequest()->getParam('product_id'), 'qty' => '1'];
                $this->cart->truncate();
                $this->cart->addProduct($productobj, $params);
                $this->cart->save();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something Went Wrong'));
                $this->_redirect('*/*/view');
                return;
            }

            $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
            $this->_customerSession->setBeforeAuthUrl($this->urlModel->create()->getUrl('checkout/index/index', ['_secure' => true, '_current' => true]));
            $this->_redirect('customer/account/create');
            return;
        }

        if (!empty($existing_subcription)) {
            $existing_subcription = $this->helper->getExistingSubcription($this->_customerSession->getCustomerId());
            $productId = $this->helper->getProductIdByMembershipId($existing_subcription[0]['membership_id']);

            if ($productId == $this->getRequest()->getParam('product_id')) {
                $this->messageManager->addError(__('You have alrady subscribed this package'));
                $this->_redirect('*/*/view');
                return;
            }
        }

        $productobj = $this->product->load($this->getRequest()->getParam('product_id'));
        if (!$productobj->getId()) {
            $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
            $this->_redirect('*/*/view');
            return;
        }

        /*if($this->getRequest()->getParam('price')==0){
            $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));

            $this->_redirect('membership/membership/confirm');

            return;
        }*/

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
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something Went Wrong'));
            $this->_redirect('*/*/view');
            return;
        }
        $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
        $this->_redirect('checkout');
        return;
    }
}
