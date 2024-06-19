<?php

namespace Matrix\NoncatRfq\Controller\Po;

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNegotiation\CollectionFactory as RfqNegotiationCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqProduct\CollectionFactory as rfqProductCollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqPo;
use Matrix\NoncatalogueRfq\Model\RfqPodetail;
use Matrix\NoncatalogueRfq\Model\RfqPodetailFactory;

class Save extends \Ced\CsMarketplace\Controller\Vendor
{

    const NONCATALOG_RFQ_PRODUCT_TYPE = 'virtual';    // type of product (simple/virtual/downloadable/configurable)

    protected $rfqNegotiationCollectionFactory;
    protected $rfqProductCollectionFactory;
    private $productRepository;
    protected $session;
    private $poPrefixHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        StoreManagerInterface $storeManager,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        RfqPo $po,
        RfqPodetail $podetail,
        RfqPodetailFactory $poDetailFactory,
        rfqProductCollectionFactory	$rfqProductCollectionFactory,
        RfqNegotiationCollectionFactory $rfqNegotiationCollectionFactory,
        \MageMonkeys\PoPrefix\Helper\Data $poPrefixHelper,
        ProductFactory $productFactory
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->_po = $po;
        $this->_podetail = $podetail;
        $this->poDetailFactory = $poDetailFactory;
        $this->productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->rfqProductCollectionFactory = $rfqProductCollectionFactory;
        $this->rfqNegotiationCollectionFactory = $rfqNegotiationCollectionFactory;
        $this->session = $customerSession;
        $this->poPrefixHelper = $poPrefixHelper;
        $this->productFactory = $productFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        //if ($id = $this->getRequest()->getParam('id')) {
        if ($data['rfq_id']>0) {
            $quote_id = $data['rfq_id'];// RFQ quote id
            try {
                $quoteData = $this->quoteFactory->create()->load($quote_id);
                $customer_id = $quoteData->getCustomerId();
                $qty =  $quoteData->getRfqTotalQty(); //$quoteData->getQuoteUpdatedQty();
                $price = $quoteData->getRfqTotalPrice();//$quoteData->getQuoteUpdatedPrice();
                $store_id = $quoteData->getStoreId();
                $quotetotalproducts = 0;
                $po_qty = 0;
                $pocollection = $this->_po->getCollection();
                $item_info = [];
                $negotiation_id =  $this->getNegotiationAccpectedId();
                $pocollection = $this->_po->getCollection();
                if (sizeof($pocollection) > 0) {
                    $po_id =  $pocollection->getLastItem()->getPoId();
                    $po_id++;
                    $poincId = $this->poPrefixHelper->getPoPrefix('non_cat_rfq') . $store_id . '0' . $po_id;
                } else {
                    $poincId = $this->poPrefixHelper->getPoPrefix('non_cat_rfq') . $store_id . '01';
                }
                $item_info['sku'] = $poincId . "_1";

                $collections = $this->rfqProductCollectionFactory->create()->addFieldToFilter('rfq_id', $quote_id);
                if($collections->getSize()) {
                    $firstItem =  $collections->getFirstItem();
                    $productResult = $firstItem->toArray();
                    $item_info['name'] = $productResult['name'];
                    $item_info['desc'] = $productResult['desc'];
                    $item_info['item_identifier'] = $productResult['item_identifier'];
                    $item_info['qty'] = $productResult['qty'];
                    $item_info['target_price'] = $productResult['target_price'];
                }

                /**
                 * Item Qty and Price will be changed if Accpected negotiation exist
                 */
                if($negotiation_id>0) {
                    $vendor_id = $this->getVendorId();
                    $collection = $this->rfqNegotiationCollectionFactory->create()
                    ->addFieldToFilter('id', $negotiation_id)
                    ->addFieldToFilter('quote_id', $quote_id)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->addFieldToFilter('is_accpected', ['eq'=>1]);
                    $negotioItem =  $collection->getFirstItem();
                    $item_info['qty']  = $negotioItem->getData('negotiotion_qty');
                    $item_info['target_price']  = $negotioItem->getData('negotiotion_price');
                }

                //Create RFQ Product
                $product_type =  self::NONCATALOG_RFQ_PRODUCT_TYPE;
                $order_id = 0;
                $quote_id = 0;
                $parent_id = 0;
                $vendor_id =  $this->getVendorId();
                $product_qty = $item_info['qty'];
                $quoted_qty  = $item_info['qty'];
                $quoted_price  = $item_info['target_price'];
                $po_price  = $product_qty * $quoted_price;
                $remaining_qty 	= 0;
                $custom_option 	= json_encode($productResult);
                $product_id = $this->createRfqProuct($item_info); //Create Non-catalog RFQ Prduct
                if(!isset($product_id) || $product_id<=0) {
                    $this->messageManager->addErrorMessage(__('Something went wrong.Unable to create RFQ Product'));
                    return $this->_redirect('vendornoncatrfq/rfq/view', ['id' => $this->getRequest()->getParam('id')]);
                }
                //Create PO Details
                $po_detail = $this->poDetailFactory->create();
                $po_detail->setData('po_id', $poincId);
                $po_detail->setData('quote_id', $quoteData->getData('rfq_id'));
                $po_detail->setData('product_id', $product_id);
                $po_detail->setData('parent_id', $parent_id);
                $po_detail->setData('vendor_id', $vendor_id);
                $po_detail->setData('product_qty', $product_qty);
                $po_detail->setData('quoted_qty', $quoted_qty);
                $po_detail->setData('quoted_price', $quoted_price);
                $po_detail->setData('po_price', $po_price);
                $po_detail->setData('remaining_qty', $remaining_qty);
                $po_detail->setData('custom_option', $custom_option);
                $po_detail->setData('po_price', $po_price);
                $po_detail->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
                $po_detail->setData('product_type', $product_type);
                $po_detail->setData('name', $item_info['name']);
                $po_detail->setData('sku', $item_info['sku']);
                $po_detail->save();

                //Create PO
                $this->_po->setData('po_increment_id', $poincId);
                $this->_po->setData('order_id', 0);
                $this->_po->setData('rfq_id', $quoteData->getData('rfq_id'));
                $this->_po->setData('vendor_id', $vendor_id);
                $this->_po->setData('quote_updated_qty', $qty);
                $this->_po->setData('quote_updated_price', $price);
                $this->_po->setData('po_qty', $qty);
                $this->_po->setData('po_price', $price);
                $this->_po->setData('remaining_price', 0);
                $this->_po->setData('po_customer_id', $customer_id);
                $this->_po->setData('rfq_fees', 0);
                $this->_po->setData('is_feespaid', 0);
                $this->_po->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
                $this->_po->save();

                //Update RFQ satus
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED);
                $quoteData->save();

                $message = '';
                $this->messageManager->addSuccessMessage(__('Po was successfully created.' . $message));
                return $this->_redirect('vendornoncatrfq/po/index');
                /*if ($currentQuote && $currentQuote->getRfqId()) {
                    $this->_coreRegistry->register('matrix_current_rfq', $currentQuote);
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->prepend(__('Create Proposal for # %1', $currentQuote->getQuoteIncrementId()));
                    return $resultPage;
                } */

            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the PO. Kindly enter the correct data.'));
                return $this->_redirect('vendornoncatrfq/rfq/view', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
            return $this->_redirect('vendornoncatrfq/rfq/view', ['id' => $this->getRequest()->getParam('id')]);
        }
    }

    private function getVendorId()
    {
        return $this->session->getVendorId();
    }

    public function getNegotiationAccpectedId()
    {
        $vendor_id = $this->getVendorId();
        $collection = $this->rfqNegotiationCollectionFactory->create()
        ->addFieldToFilter('quote_id', $this->getRequest()->getParam('id'))
        ->addFieldToFilter('vendor_id', $vendor_id)
        ->addFieldToFilter('is_accpected', ['eq'=>1]);
        //echo $collection->getSelect();
        //return $collection->getFirstItem();
        return  $collection->getFirstItem()->getId();

    }

    private function createRfqProuct($data)
    {
        $existRfqProduct =  $this->loadMyProduct($data['sku']);
        if(isset($existRfqProduct)) {
            return $existRfqProduct->getId();
        }
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $product = $this->productFactory->create();
        $product->setSku($data['sku']); // Set your sku here
        $product->setName($data['name']); // Name of Product
        //$product->setDdescription($data['desc']); // Name of Product
        $product->setAttributeSetId(4); // Attribute set id
        $product->setStatus(1); // Status on product enabled/ disabled 1/0
        $product->setUrlKey('noncatalogrfq-' . $data['sku']); // Status on product enabled/ disabled 1/0
        $product->setWeight(0); // weight of product
        $product->setVisibility(2); // visibilty of product (catalog / search / catalog, search / Not visible individually)
        $product->setTaxClassId(0); // Tax class id
        $product->setTypeId(self::NONCATALOG_RFQ_PRODUCT_TYPE); // type of product (simple/virtual/downloadable/configurable)
        //$product->setTypeId('simple');
        $product->setPrice($data['target_price']); // price of product
        $product->setWebsiteIds([$websiteId]); // Website
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

    public function loadMyProduct($sku)
    {
        try {
            return $this->productRepository->get($sku);
        } catch(\Exception $e) {
            return null;
            //$this->messageManager->addErrorMessage($e->getMessage());
            //return $this->_redirect('noncatalogrequesttoquote/customer/editpo', ['poId' => $poData->getId()]);
        }
    }
}
