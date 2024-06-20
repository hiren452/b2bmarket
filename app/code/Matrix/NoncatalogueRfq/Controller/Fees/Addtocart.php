<?php
namespace Matrix\NoncatalogueRfq\Controller\Fees;

use Ced\CustomerMembership\Helper\Data as customerMembershipHelper;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPodetail\CollectionFactory;

use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

class Addtocart extends \Magento\Framework\App\Action\Action
{
    const RFQ_FEES_PRODUCT_TYPE = 'virtual';

    private $productRepository;

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

    protected $subscriptionFactory;

    protected $subscriptionCollectionFactory;

    protected $customerMembershipHelper;

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
        \Magento\Catalog\Model\ProductRepository $productRepository,
        RfqPoFactory $poFactory,
        NoncatalogRfqFactory $quoteFactory,
        CollectionFactory $poDetailCollectionFactory,
        StoreManagerInterface $storeManager,
        SubscriptionFactory $subscriptionFactory,
        customerMembershipHelper $customerMembershipHelper,
        \Ced\CustomerMembership\Model\ResourceModel\Subscription\CollectionFactory $subscriptionCollectionFactory,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->poFactory = $poFactory;
        $this->quoteFactory = $quoteFactory;
        $this->poDetailCollectionFactory = $poDetailCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->customerMembershipHelper = $customerMembershipHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        if (! $this->session->isLoggedIn()) {
            $this->session->setBeforeAuthUrl($this->_url->getUrl('noncatalogrequesttoquote/quotes/addtocart', ['po_incId' => $poIncid]));
            $this->messageManager->addErrorMessage(__('Please login first'));
            return $this->_redirect('customer/account/login');
        }

        $fees = $this->calculateNonCatRfqFees();
        if ($fees<=0) {
            $this->_redirect('membership/membership/view/');
            $this->messageManager->addErrorMessage(__('Unable to calculate Fees.Please subscribe to membership plan.'));
            return;
        }
        $quote_id = $this->getRequest()->getParam('quote_id');
        $poid = $this->getRequest()->getParam('poid');
        $nonCatalogRfq = $this->quoteFactory->create()->load($quote_id);
        $po = $this->poFactory->create()->load($poid);
        $customerId = $nonCatalogRfq->getCustomerId();
        $customer_id = $this->session->getCustomer()->getId();
        $customeremail = $nonCatalogRfq->getCustomerEmail();
        $productid = 0;
        /*if($nonCatalogRfq->getData('is_feespaid')==1 && $nonCatalogRfq->getData('rfq_fees')>0){
            echo "Fees Paid <br/>";
        } else {
            echo "Fees Not  Paid <br/>";
        }*/
        $product = $this->loadRfqFeesProduct('noncatalogrfq_fees');
        if ($product) {
            $productid = $product->getEntityId();
            $name = $product->getName();
        } else {
            $this->messageManager->addErrorMessage(__('Non-catalog RFQ Fess Not Exist.You are not allowed to checkout Non-Catalog RFQ'));
            return $this->_redirect('noncatalogrequesttoquote/customer/editpo/', ['poId'=>$poid]);
        }

        if ($customer_id != $customerId) {
            $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update the available quotes only.'));
            return $this->_redirect('customer/account/index');
        }

        if ($customeremail == $this->session->getCustomer()->getEmail()) {
            //if($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED || $status == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING )
            if ($po->getData('is_feespaid')<=0 && $po->getData('rfq_fees')<=0) {
                if ($productid>0) {
                    $data['rfq_id'] = $nonCatalogRfq->getData('rfq_id');
                    $data['product_qty'] = 1;
                    $data['product_id'] = $productid;
                    $data['parent_id'] = 0;
                    $data['rfq_fees'] = $fees;
                    $this->addRfqFeesToCart($productid, $data); //Add RFQ Fees to cart
                    return $this->_redirect("checkout/cart/index");
                } else {
                    $this->messageManager->addErrorMessage(__('Non-catalog Fess not exist.'));
                    return $this->_redirect('noncatalogrequesttoquote/customer/editpo/', ['poId'=>$poid]);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Non-catalog Fess already paid.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/editpo/', ['poId'=>$poid]);
            }
        }
    }

    public function addRfqFeesToCart($productid, $data)
    {
        $rfqId = $data['rfq_id'];
        $poid = $this->getRequest()->getParam('poid');
        $cart = $this->cart;
        $cartItems = $cart->getQuote()->getAllItems();
        $rfqFeesItemsAlreadyExistFlag = false;
        $existRfqFeesId = '';
        foreach ($cartItems as $item) {
            if ($item->getMatrixRfqfeesId()) {
                $existRfqFeesId = $item->getMatrixRfqfeesId();
                $rfqFeesItemsAlreadyExistFlag = true;
                break;
            }
        }
        if ($rfqFeesItemsAlreadyExistFlag && $existRfqFeesId) {
            return $this->_redirect('checkout/index/cart');
        }
        $ermsg = false;

        if ($data['product_qty'] > 0) {
            $productid = $data['product_id'];
            $product_id = $data['parent_id'];
            if (!$data['parent_id']) {
                $product_id = $data['product_id'];
            }
            $quantity = $data['product_qty'];
            $price = $data['rfq_fees'];
            if (in_array($productid, $cart->getQuoteProductIds())) {
                if (!$ermsg) {
                    $this->messageManager->addErrorMessage(__("Cant add all products, some already exist in cart ."));
                    $ermsg = true;
                }
            }
            /*START addtocart*/
            $productobj = $this->productFactory->create()->load($productid);
            $productobj->setPrice($price);
            $params = ['qty' => $quantity,'price'=>$price];
            $cart->addProduct($productobj, $params);
            $cart->getQuote()->getItemByProduct($productobj)->setMatrixRfqfeesId($poid);
            $cart->save();
            /*END addtocart*/
        }
    }

    public function loadRfqFeesProduct($sku)
    {
        $price = $this->calculateNonCatRfqFees();
        try {
            $product = $this->productRepository->get($sku);
            /* temp fix to update correct fee to the product*/
            $product->setPrice($price);
            $product->save();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = false;
        }
        if ($product) {
            return $product;
        } else {
            $data['sku'] = $sku;
            $data['name'] = 'Non-catalog RFQ Fees';
            $data['desc'] = 'Non-catalog RFQ Fees paid by Buyer';
            $data['attribute_setid'] = 4;
            $data['status'] = 1;
            $data['weight'] = 0;
            $data['visibility'] = 3;
            $data['tax_classid'] = 0;
            $data['type_id'] = self::RFQ_FEES_PRODUCT_TYPE;
            //$data['type_id'] = 'simple';
            $data['price'] = $price;
            $data['qty'] = 1;
            $productid =$this->createRfqFeesProuct($data);
            if ($productid>0) {
                $this->loadRfqFeesProduct($sku);
            } else {
                return null;
            }
        }
    }

    private function createRfqFeesProuct($data)
    {
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $product = $this->productFactory->create();
        $product->setSku($data['sku']);
        $product->setName($data['name']);
        $product->setAttributeSetId($data['attribute_setid']);
        $product->setStatus($data['status']);
        $product->setUrlKey('noncatalogrfqfees-' . $data['sku']);
        $product->setWeight($data['weight']);
        $product->setVisibility($data['visibility']);
        $product->setTaxClassId($data['tax_classid']);
        $product->setTypeId(self::RFQ_FEES_PRODUCT_TYPE);
        $product->setPrice($data['price']);
        $product->setWebsiteIds([$websiteId]);
        $product->setStockData(
            [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 0,
                        'is_in_stock' => 1,
                        'qty' => $data['qty']
                    ]
        );
        $newproduct = $product->save();
        return $newproduct->getId();
    }

    public function calculateNonCatRfqFees()
    {
        $nonCatalogRfqFees = 0;
        $customerId = $this->session->getCustomer()->getId();
        $subscriptionArr = $this->customerMembershipHelper->getExistingSubcription($customerId);
        if (is_array($subscriptionArr) && count($subscriptionArr)) {
            $subscriptionArr = $subscriptionArr[0];
            $nonCatalogRfqFees = $subscriptionArr['noncatrfq_fee'];
        }
        return $nonCatalogRfqFees;
    }

    /*public function calculateNonCatRfqFees(){
        $status = "running";
        $rfqFees = 0;
        $customerId = $this->session->getCustomer()->getId();
        $now = new \DateTime();
        $cur_time   =  date('Y-m-d');
        $matrix_rfqmembership_fees_tbl = 'matrix_rfqmembership_fees';
        $second_table_name = 'matrix_rfqmembership_fees';
        $collection = $this->subscriptionFactory->create()->getCollection()
        ->addFieldToSelect(array('plan_name','customer_id','start_date','end_date'))
        ->addFieldToFilter('customer_id', $customerId)
         ->addFieldToFilter('status', $status);

        $collection->getSelect()->joinInner(array('second' => $second_table_name),'main_table.membership_id = second.customermembership_id');
        $membsrShip =  array();
        if($collection->getSize()<=0){
            return $rfqFees;
        }
        foreach ($collection as $subcription) {
            $end_date = date_create($subcription->getData('end_date'));
            $curr_date= date_create($cur_time);
            $interval = date_diff($curr_date,$end_date,false);
            if ($interval->format('%a')>0) {
                $membsrShip = $subcription->getData();
            }
        }
        if(count($membsrShip)){

            $rfqFees  = $membsrShip['rfq_fees'];
        }

        return $rfqFees;

    }*/
}
