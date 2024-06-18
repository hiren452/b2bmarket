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

use Ced\RequestToQuote\Model\PoFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\PoDetail\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Addtocart extends Action
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
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CollectionFactory
     */
    protected $poDetailCollectionFactory;

    /**
     * Addtocart constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param Cart $cart
     * @param ProductFactory $productFactory
     * @param PoFactory $poFactory
     * @param QuoteFactory $quoteFactory
     * @param CollectionFactory $poDetailCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Cart $cart,
        ProductFactory $productFactory,
        PoFactory $poFactory,
        QuoteFactory $quoteFactory,
        CollectionFactory $poDetailCollectionFactory,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->poFactory = $poFactory;
        $this->quoteFactory = $quoteFactory;
        $this->poDetailCollectionFactory = $poDetailCollectionFactory;
        parent::__construct($context, $customerSession, $cart, $data);

    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $poIncid = $this->getRequest()->getParam('po_incId');
        if (! $this->session->isLoggedIn()) {
            $this->session->setBeforeAuthUrl($this->_url->getUrl('requesttoquote/quotes/addtocart', ['po_incId' => $poIncid]));
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $poData = $this->poFactory->create()->load($poIncid, 'po_increment_id');
        try {
            $poentityId = $poData->getData('po_id');
            $status = $poData->load($poentityId)->getStatus();

            $quote_id = $poData->getData('quote_id');
            $customeremail = $this->quoteFactory->create()->load($quote_id)->getCustomerEmail();
            if ($customeremail == $this->session->getCustomer()->getEmail()) {
                if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED || $status == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING) {
                    $setValue = $this->poFactory->create()->load($poentityId);
                    $setValue->setData('status', '1');
                    $setValue->save();
                    $podetail = $poData->getCollection()->addFieldToFilter('po_increment_id', $poIncid)->addFieldToFilter('status', '1')->getData();
                    if (count($podetail) > 0) {
                        $poProd = $this->poDetailCollectionFactory->create()->addFieldToFilter('po_id', $poIncid)->getData();
                        $cart = $this->cart;
                        $currentQuote = $cart->getQuote();
                        $cartItems = $currentQuote->getAllItems();
                        foreach ($cartItems as $item) {
                            $currentQuote->removeItem($item->getId());
                        }
                        $prod_price = [];
                        if (!empty($this->session->getRfqPrice())) {
                            $prod_price = $this->session->getRfqPrice();
                        }
                        $ermsg = false;
                        foreach ($poProd as $data) {
                            if ($data['product_qty'] > 0) {
                                $productid = $data['product_id'];
                                $product_id = $data['parent_id'];
                                if (!$data['parent_id']) {
                                    $product_id = $data['product_id'];
                                }
                                $quantity = $data['product_qty'];
                                $price = $data['po_price'];
                                $unit_price = floatval($data['po_price']/$data['product_qty']);

                                if (in_array($productid, $cart->getQuoteProductIds())) {
                                    if (!$ermsg) {
                                        $this->messageManager->addErrorMessage(__("Cant add all products, some already exist in cart ."));
                                        $ermsg = true;
                                    }
                                    continue;
                                }
                                if (!empty($data['custom_option'])) {
                                    $productobj = $this->productFactory->create()->load($product_id);
                                    $prod_price[$productid]['prev_price'] = $productobj->getPrice();
                                    $prod_price[$productid]['new_price'] = $unit_price;
                                    $this->session->setRfqPrice($prod_price);
                                    $productobj->setPrice($unit_price);
                                    $customOption = json_decode($data['custom_option'], true);
                                    $params = [
                                      'product' => $product_id,
                                      'super_attribute' => $customOption['super_attribute'],
                                      'qty' => $data['product_qty']
                                    ];
                                    $cart->addProduct($productobj, $params);
                                    $cart->getQuote()->getItemByProduct($productobj)->setCedPoId($poentityId);
                                } else {
                                    $productobj = $this->productFactory->create()->load($productid);
                                    $prod_price[$productid]['prev_price'] = $productobj->getPrice();
                                    $prod_price[$productid]['new_price'] = $unit_price;

                                    $this->session->setRfqPrice($prod_price);
                                    $productobj->setPrice($unit_price);
                                    $params = [
                                        'qty' => $data['product_qty']
                                    ];
                                    $cart->addProduct($productobj, $params);
                                    $cart->getQuote()->getItemByProduct($productobj)->setCedPoId($poentityId);
                                }
                            }
                        }
                        $cart->save();
                        return $resultRedirect->setPath('checkout/index/index');
                    } else {
                        $this->messageManager->addErrorMessage(__("Invalid Request"));
                        return $resultRedirect->setPath('requesttoquote/customer/po');
                    }
                } else {
                    if($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_ORDERED) {
                        $this->messageManager->addErrorMessage(__("This PO has already been ordered."));
                        return $resultRedirect->setPath('/');
                    } elseif($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_DECLINED) {
                        $this->messageManager->addErrorMessage(__("This PO has been already cancelled."));
                        return $resultRedirect->setPath('/');
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__("You can't proceed with other customer data"));
                return $resultRedirect->setPath('customer/account/index');
            }
        } catch(\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('requesttoquote/customer/editpo', ['poId' => $poData->getId()]);
        }
    }
}
