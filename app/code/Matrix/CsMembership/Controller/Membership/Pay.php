<?php

namespace Matrix\CsMembership\Controller\Membership;

class Pay extends \Ced\CsMarketplace\Controller\Vendor
{
    public $formkey;
    protected $messageManager;

    protected $membershipHelper;

    protected $productFactory;

    protected $cart;

    protected $productRepository;

    protected $scopeConfig;

    protected $alaCartFactory;

    protected $timezoneInterface;

    protected $alaCartPriceFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Matrix\CsMembership\Model\AlaCartFactory $alaCartFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Matrix\CsMembership\Model\AlaCartPriceFactory $alaCartPriceFactory
    ) {
        $this->messageManager    = $messageManager;

        $this->membershipHelper  = $membershipHelper;
        $this->productFactory    = $productFactory;
        $this->formkey           = $formkey;
        $this->cart              = $cart;
        $this->productRepository = $productRepository;
        $this->scopeConfig       = $scopeConfig;
        $this->alaCartFactory    = $alaCartFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->alaCartPriceFactory = $alaCartPriceFactory;

        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        /* check subscription */
        $existing_subscription = $this->membershipHelper->getExistingSubcription($this->_getSession()->getVendorId());

        if (empty($existing_subscription)) {
            $this->messageManager->addErrorMessage(__('Please take a subscription first.'));
            return $this->_redirect("csmembership/membership/index");
        }

        $params = $this->getRequest()->getParams();
        $price  = $this->calculatePrice($params);

        /* create virtual product */
        if (!empty($price)) {
            $now = $this->timezoneInterface->date()->format('Y-m-d H:i:s');

            $sku  = 'alacartfee' . date('ymdhis');
            $name = __('Ala Cart Fees');
            $product = $this->productFactory->create();
            $product
                ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
                ->setAttributeSetId($this->productFactory->create()->getDefaultAttributeSetId())
                ->setSku($sku)
                ->setWebsiteIds([1])
                ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
                ->setStockData([
                    'manage_stock' => 0,
                    'is_in_stock' => 1,
                    'qty' => 1,
                    'min_sale_qty' => 1,
                    'max_sale_qty' => 1
                ])
                ->setName($name)
                ->setShortDescription($name)
                ->setDescription($name)
                ->setPrice($price)
                ->setTaxClassId(2)// Taxable Goods by default
                ->setWeight(10);

            try {
                $product->save();
                $product_id = $this->productFactory->create()->getIdBySku($sku);

                $alaCart = $this->alaCartFactory->create();

                $alaCart->setSubscriptionId($existing_subscription[0]['subscription_id']);
                $alaCart->setVendorId($this->_getSession()->getVendorId());
                $alaCart->setVProductId($product_id);
                $alaCart->setProductQty($params['product_qty']);
                $alaCart->setAuctionQty($params['auction_qty']);
                $alaCart->setRfqQty($params['rfq_qty']);
                $alaCart->setNonCatalogRfqQty($params['non_catalog_rfq_qty']);
                $alaCart->setCreated($now);
                $alaCart->save();

                /* Add to cart and redirect to checkout */
                $this->proceedToCart($product_id);
                return $this->_redirect("checkout/index/index");
                /* Add to cart and redirect to checkout */

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
        }
    }

    public function calculatePrice($data)
    {
        $product_qty = $data['product_qty'];
        $auction_qty = $data['auction_qty'];
        $rfq_qty     = $data['rfq_qty'];
        $non_catalog_rfq_qty = $data['non_catalog_rfq_qty'];
        $total_price = 0;
        $product_price = 0;
        $auction_price = 0;
        $rfq_price     = 0;
        $non_catalog_rfq_price = 0;
        $collections = $this->alaCartPriceFactory->create()->getCollection();

        foreach ($collections as $collection) {
            /* Product */
            if (!empty($product_qty) && strtolower($collection->getName()) == 'product') {
                $product_price = $this->getThePrice($collection->getCommission(), $product_qty);
            }
            /* Auction */
            if (!empty($auction_qty) && strtolower($collection->getName()) == 'auction') {
                $auction_price = $this->getThePrice($collection->getCommission(), $auction_qty);
            }

            /* RFQ */
            if (!empty($rfq_qty) && strtolower($collection->getName()) == 'rfq') {
                $rfq_price = $this->getThePrice($collection->getCommission(), $rfq_qty);
            }

            /* Non Catalog RFQ */
            if (!empty($non_catalog_rfq_qty) && strtolower($collection->getName()) == 'noncatalogrfq') {
                $rfq_price = $this->getThePrice($collection->getCommission(), $non_catalog_rfq_qty);
            }
        }

        $total_price = $product_price + $auction_price + $rfq_price + $non_catalog_rfq_price;
        return $total_price;
    }

    protected function getThePrice($commissions, $qty)
    {
        $_price = 0;
        $commissions = json_decode($commissions);
        foreach ($commissions as $commission) {

            if (isset($commission->qty_from) && isset($commission->qty_to) && $qty >= $commission->qty_from && $qty <= $commission->qty_to) {

                $_price = (float)$commission->price;
                break;
            }

            if (isset($commission->qty_above) && $qty >= $commission->qty_above) {
                $_price = (float)$commission->above_price;
                break;
            }
        }
        return $_price;
    }

    public function proceedToCart($product_id)
    {
        $product = $this->getProduct($product_id);

        $params = [
            'formkey'=> $this->formkey->getFormKey(),
            'product'=> $product_id,
            'price'  => $product->getPrice(),
            'qty'    => 1
        ];
        $this->cart->addProduct($product, $params);
        $this->cart->save();
        return true;
    }

    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }
}
