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
 * @category  Ced
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Auction\Controller\Cart;

use Ced\Auction\Model\ResourceModel\Winner\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Registry;

class Add extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Add constructor.
     *
     * @param Context  $context
     * @param FormKey  $formkey
     * @param Cart     $cart
     * @param Product  $product
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        FormKey $formkey,
        Cart $cart,
        ProductRepository $productRepository,
        Registry $registry,
        CollectionFactory $winnerCollection,
        Session $customerSession,
        array $data = []
    ) {
        $this->formkey = $formkey;
        $this->cart = $cart;
        $this->product = $productRepository;
        $this->registry = $registry;
        $this->winnerCollection = $winnerCollection;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $product= $this->getRequest()->getParams();
        $params = [
            'formkey'=> $this->formkey->getFormKey(),
            'product'=> $product['id'],
            'price'=>$product['price'],
            'qty'=> 1
        ];

        $this->registry->register('price', $product['price']);
        $this->registry->register('productId', $product['id']);

        $this->customerSession->setAuctionPrice($product['price']);

        $loadedProduct = $this->product->getById($product['id']);
        $this->cart->addProduct($loadedProduct, $params);
        $this->cart->save();

        $winnerData = $this->winnerCollection->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomer()->getId())
            ->addFieldToFilter('product_id', $product['id'])
            ->addFieldToFilter('status', 'not purchased')
            ->getLastItem();
        $winnerData->setData('add_to_cart', 1);
        $winnerData->save();

        // return $this->_redirect("checkout/cart/add/form_key/", $params);
        return $this->_redirect("auction/index/index");
    }
}
