<?php

namespace Matrix\CustomerMembership\Plugin\Controller\Membership;

class Subscribe
{
    protected $resultPageFactory;

    protected $_checkoutSession;

    protected $_customerSession;

    protected $cart;

    protected $helper;

    protected $product;

    protected $messageManager;

    protected $redirect;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\CustomerMembership\Helper\Data $helper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        $this->cart = $cart;
        $this->_customerSession = $customerSession;
        $this->urlModel = $urlFactory;
        $this->product = $product;
        $this->_checkoutSession = $checkoutSession;
        $this->formKey = $formkey;
        $this->helper  = $helper;
        $this->product = $productModel;
        $this->messageManager = $messageManager;
        $this->redirect = $redirectFactory;
    }

    public function aroundExecute(
        \Ced\Customermembership\Controller\Membership\Subscribe $subject,
        callable $proceed
    ) {
        if (!$this->_customerSession->isLoggedIn()) {
            $productobj = $this->product->load($this->getRequest()->getParam('product_id'));

            if (!$productobj->getId()) {
                $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
                $resultRedirect = $this->redirect->create();
                $resultRedirect->setPath('*/*/view');
                //$this->_redirect('*/*/view');
                return $resultRedirect;
            }

            try {
                $params = ['product' => $this->getRequest()->getParam('product_id'), 'qty' => '1'];
                $this->cart->truncate();
                $this->cart->addProduct($productobj, $params);
                $this->cart->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something Went Wrong'));
                $resultRedirect = $this->redirect->create();
                $resultRedirect->setPath('*/*/view');
                //$this->_redirect('*/*/view');
                return $resultRedirect;
            }

            $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
            $this->_customerSession->setBeforeAuthUrl($this->urlModel->create()->getUrl('checkout/index/index', ['_secure' => true, '_current' => true]));
            $resultRedirect = $this->redirect->create();
            $resultRedirect->setPath('customer/account/create');
            //$this->_redirect('customer/account/create');
            return $resultRedirect;
        }

        if (!empty($existing_subcription)) {
            $existing_subcription = $this->helper->getExistingSubcription($this->_customerSession->getCustomerId());
            $productId = $this->helper->getProductIdByMembershipId($existing_subcription[0]['membership_id']);

            if ($productId == $this->getRequest()->getParam('product_id')) {
                $this->messageManager->addError(__('You have alrady subscribed this package'));
                $resultRedirect = $this->redirect->create();
                $resultRedirect->setPath('*/*/view');
                //$this->_redirect('*/*/view');
                return $resultRedirect;
            }
        }

        $productobj = $this->product->load($this->getRequest()->getParam('product_id'));

        if (!$productobj->getId()) {
            $this->messageManager->addErrorMessage(__('Product Does Not Exist.Contact Administrator'));
            $resultRedirect = $this->redirect->create();
            $resultRedirect->setPath('*/*/view');
            //$this->_redirect('*/*/view');
            return $resultRedirect;
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
            $resultRedirect = $this->redirect->create();
            $resultRedirect->setPath('*/*/view');
            //$this->_redirect('*/*/view');
            return $resultRedirect;
        }
        $this->_checkoutSession->setMembershipProductid($this->getRequest()->getParam('product_id'));
        $resultRedirect = $this->redirect->create();
        $resultRedirect->setPath('checkout');
        //$this->_redirect('checkout');
        return $resultRedirect;
    }
}
