<?php

namespace Matrix\CsAuction\Controller\AuctionList;

/**
 * Class Pay
 * @package Matrix\CsAuction\Controller\AuctionList
 */
class Pay extends \Ced\CsMarketplace\Controller\Vendor
{
    protected $messageManager;

    protected $auctionFactory;

    protected $membershipHelper;

    protected $productFactory;
    protected $cart;

    protected $productRepository;
    protected $indexerRegistry;

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
        \Ced\Auction\Model\AuctionFactory $auctionFactory,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->messageManager    = $messageManager;

        $this->auctionFactory    = $auctionFactory;
        $this->membershipHelper  = $membershipHelper;
        $this->productFactory    = $productFactory;
        $this->formkey           = $formkey;
        $this->cart              = $cart;
        $this->productRepository = $productRepository;
        $this->indexerRegistry = $indexerRegistry;

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

        $id      = $this->getRequest()->getParam('id');
        $auction = $this->auctionFactory->create()->load($id);

        if($auction->getData('is_paid')) {
            return $this->_redirect("csauction/auctionlist/index");
        } else {
            /* check already saved virtual product */
            $v_product_id = $auction->getData('v_product_id');
            if(!empty($v_product_id)) {
                $this->proceedToCart($v_product_id);
                return $this->_redirect("checkout/index/index");
            }

            /* create virtual product */
            $existing_subscription = $this->membershipHelper->getExistingSubcription($this->_getSession()->getVendorId());

            if(empty($existing_subscription)) {
                $this->messageManager->addErrorMessage(__('Please take a subscription first.'));
                return $this->_redirect("csmembership/membership/index");
            }

            if(isset($existing_subscription[0]['auction_fee'])) {
                $price = $existing_subscription[0]['auction_fee'];

                $sku     = 'auctionfee' . rand();
                $name = __('Auction Fee');
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
                    ->setTaxClassId(2)
                    ->setWeight(0);
                try {
                    // $product1 = $this->productRepository->save($product);
                    $product->save();

                    if($product->getId()) {
                        $this->reindexProduct($product->getId());
                    }
                    $product_id = $this->productFactory->create()->getIdBySku($sku);

                    /* save the virtual product id for future use */
                    $auction->setData('v_product_id', $product_id);
                    $auction->save();

                    /* Add to cart and redirect to checkout */
                    $this->proceedToCart($product_id);
                    return $this->_redirect("checkout/index/index");
                    /* Add to cart and redirect to checkout */

                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }

    }

    private function reindexProduct($id)
    {
        $indexList = [
            'catalog_category_product',
            'catalog_product_category',
            'catalog_product_attribute',
            'cataloginventory_stock',
            'inventory',
            'catalogsearch_fulltext',
            'catalog_product_price',
        ];
        try {
            foreach ($indexList as $index) {
                $Indexer = $this->indexerRegistry->get($index);
                $Indexer->reindexList([$id]);
            }
        } catch(\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    public function proceedToCart($product_id)
    {
        try {
            $isExist = false;
            $product = $this->getProduct($product_id);
            foreach($this->cart->getQuote()->getAllItems() as $item) {
                if($item->getProductId() == $product_id) {
                    $isExist = true;
                    break;
                }
            }
            if(!$isExist) {
                $params = [
                    'formkey'=> $this->formkey->getFormKey(),
                    'product'=> $product_id,
                    'price'  => $product->getPrice(),
                    'qty'    => 1
                ];
                $this->cart->addProduct($product, $params);
                $this->cart->save();
            }
            return true;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }
}
