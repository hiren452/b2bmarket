<?php
namespace Matrix\NoncatalogueRfq\Controller\Quotes;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPodetail\CollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

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
        RfqPoFactory $poFactory,
        NoncatalogRfqFactory $quoteFactory,
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

    public function execute()
    {
        try {

            $poIncid = $this->getRequest()->getParam('po_incId');

            if (! $this->session->isLoggedIn()) {
                $this->session->setBeforeAuthUrl($this->_url->getUrl('noncatalogrequesttoquote/quotes/addtocart', ['po_incId' => $poIncid]));
                $this->messageManager->addErrorMessage(__('Please login first'));
                return $this->_redirect('customer/account/login');
            }

            $poData = $this->poFactory->create()->load($poIncid, 'po_increment_id');
            $po_id = $poData->getData('po_id');
            $quote_id = $poData->getData('rfq_id');
            $customer_id = $this->session->getCustomer()->getId();
            $nonCatalogRfq = $this->quoteFactory->create()->load($quote_id);
            $customerId = $nonCatalogRfq->getCustomerId();
            if ($customer_id != $customerId) {//Check Same Customer
                $this->messageManager->addErrorMessage(__('You are not allowed to AddtoCart of this quote.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/quotes/');
            }

            //Check if Fees amount paid
            if ($poData->getData('is_feespaid')!=1 || $poData->getData('rfq_fees')==0) {
                return $this->_redirect('noncatalogrequesttoquote/fees/addtocart/', ['quote_id'=>$quote_id,'poid'=>$po_id]);
            }

            $poentityId = $poData->getData('po_id');
            $status = $poData->load($poentityId)->getStatus();

            $customeremail = $nonCatalogRfq->getCustomerEmail();

            if ($customeremail == $this->session->getCustomer()->getEmail()) {

                if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED || $status == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING) {

                    $setValue = $this->poFactory->create()->load($poentityId);
                    $setValue->setData('status', '1');
                    $setValue->save();
                    $podetail = $poData->getCollection()->addFieldToFilter('po_increment_id', $poIncid)->addFieldToFilter('status', '1')->getData();

                    if (sizeof($podetail) > 0) {
                        $poProd = $this->poDetailCollectionFactory->create()->addFieldToFilter('po_id', $poIncid)->getData();
                        $cart = $this->cart;
                        $cartItems = $cart->getQuote()->getAllItems();
                        $poItemsAlreadyExistFlag = false;
                        $existPoId = '';
                        foreach ($cartItems as $item) {
                            if ($item->getMatrixPoId()) {
                                $existPoId = $item->getMatrixPoId();
                                $poItemsAlreadyExistFlag = true;
                                break;
                            }
                        }
                        if ($poItemsAlreadyExistFlag && $existPoId) {
                            $link = '<a href="' . $this->_url->getUrl('noncatalogrequesttoquote/customer/editpo', ['poid' => $existPoId]) . '">' . __('Click Here') . '</a>';
                            $this->messageManager->addWarning(__('Some Item(s) already exist in cart. ' . $link . ' to remove Proposal Item(s) from cart.'));
                            return $this->_redirect('noncatalogrequesttoquote/customer/editpo', ['poid' => $poentityId]);
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
                                $price = $data['quoted_price'];
                                if (in_array($productid, $cart->getQuoteProductIds())) {
                                    if (!$ermsg) {
                                        $this->messageManager->addErrorMessage(__("Cant add all products, some already exist in cart ."));
                                        $ermsg = true;
                                    }
                                    continue;
                                }
                                /*START addtocart*/

                                $productobj = $this->productFactory->create()->load($productid);
                                //$prod_price[$productid]['prev_price'] = $productobj->getPrice();
                                //$prod_price[$productid]['new_price'] = $unit_price;
                                //$this->session->setRfqPrice($prod_price);
                                //$productobj->setPrice($unit_price);
                                $productobj->setPrice($price);
                                $params = [
                                    'qty' => $data['product_qty']
                                ];

                                $cart->addProduct($productobj, $params);
                                $cart->getQuote()->getItemByProduct($productobj)->setMatrixPoId($poentityId);
                                /*END addtocart*/
                            }
                        }
                        $cart->save();
                        return $this->_redirect("checkout/cart/index");

                    }

                } else {

                    if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_ORDERED) {
                        $this->messageManager->addErrorMessage(__("This PO has already been ordered."));
                        return $this->_redirect("/");
                    } elseif ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_DECLINED) {
                        $this->messageManager->addErrorMessage(__("This PO has been already cancelled."));
                        return $this->_redirect("/");
                    }
                }

            } else {
                $this->messageManager->addErrorMessage(__("You can't proceed with other customer data"));
                return $this->_redirect("customer/account/index");
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('noncatalogrequesttoquote/customer/editpo', ['poId' => $poData->getId()]);
        }
    }
}
