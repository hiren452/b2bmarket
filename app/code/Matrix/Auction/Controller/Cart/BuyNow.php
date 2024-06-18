<?php

namespace Matrix\Auction\Controller\Cart;

use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;

class BuyNow extends \Magento\Customer\Controller\AbstractAccount
{

    public function __construct(
        Context $context,
        FormKey $formkey,
        Cart $cart,
        ProductRepository $productRepository,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->formkey = $formkey;
        $this->cart = $cart;
        $this->product = $productRepository;
        $this->customerSession = $customerSession;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        try {

            $data = $this->getRequest()->getParams();

            $params  = [
                'formkey'=> $this->formkey->getFormKey(),
                'product'=> $data['product_id'],
                'qty'=> $data['buy_now_qty']
            ];

            $product = $this->product->getById($data['product_id']);
            $this->cart->addProduct($product, $params);
            $this->cart->save();

            $this->customerSession->setBuyNow(true);
            //return $this->_redirect("checkout/cart/add/form_key/", $params);
            return $this->_redirect("checkout/index/index");
        } catch(\Exception $e) {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}
