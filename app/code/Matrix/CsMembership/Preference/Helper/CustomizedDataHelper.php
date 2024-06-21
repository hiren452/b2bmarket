<?php

namespace Matrix\CsMembership\Preference\Helper;

use Ced\Auction\Model\AuctionFactory;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMembership\Helper\Data;
use Ced\CsMembership\Model\MembershipFactory;
use Ced\CsMembership\Model\ResourceModel\Membership;
use Ced\CsMembership\Model\ResourceModel\Subscription;
use Ced\CsMembership\Model\ResourceModel\Subscription\CollectionFactory;
use Ced\CsMembership\Model\SubscriptionFactory;
use Ced\CustomerMembership\Helper\Data as CustomerMembershipHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class CustomizedDataHelper extends Data
{
    public $resource;
    public $_customerSession;
    const STATUS_ENABLED = 1;

    /**
     * @var AuctionFactory
     */
    private $auctionFactory;
    /**
     * @var CustomerMembershipHelper
     */
    private $buyerMembershipHelper;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var Subscription
     */
    private $subscriptionResource;
    /**
     * @var CollectionFactory
     */
    private $subscriptionCollection;
    /**
     * @var Membership
     */
    private $membershipResource;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Ced\CsMembership\Model\ResourceModel\Membership\CollectionFactory
     */
    protected $membershipCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        Transaction $transaction,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        SubscriptionFactory $subscriptionFactory,
        MembershipFactory $membershipFactory,
        DateTime $dateTime,
        VendorFactory $vendorFactory,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        InvoiceService $invoiceService,
        CategoryFactory $categoryFactory,
        AuctionFactory $auctionFactory,
        CustomerMembershipHelper $buyerMembershipHelper,
        CheckoutSession $checkoutSession,
        Subscription $subscriptionResource,
        CollectionFactory $subscriptionCollection,
        Membership $membershipResource,
        \Ced\CsMembership\Model\ResourceModel\Membership\CollectionFactory $membershipCollectionFactory,
        ManagerInterface $manager,
        SerializerInterface $serelization,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Psr\Log\LoggerInterface $logger,
        ResourceConnection $resource
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $transaction,
            $messageManager,
            $transportBuilder,
            $subscriptionFactory,
            $membershipFactory,
            $dateTime,
            $vendorFactory,
            $storeManager,
            $orderFactory,
            $invoiceService,
            $categoryFactory,
            $serelization,
            $priceCurrency,
            $logger,
            $checkoutSession,
            $dateTime,
        );
        $this->auctionFactory  = $auctionFactory;
        $this->buyerMembershipHelper = $buyerMembershipHelper;
        $this->checkoutSession = $checkoutSession;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionCollection = $subscriptionCollection;
        $this->membershipResource = $membershipResource;
        $this->messageManager = $manager;
        $this->membershipCollectionFactory = $membershipCollectionFactory;
        $this->membershipFactory = $membershipFactory;
        $this->resource = $resource;
    }

    /**
     * @param $emailTemplate
     * @param int $storeId
     * @param $emailTemplateVariables
     * @param $senderEmail
     * @param $reciever_email
     */
    public function sendMail($emailTemplate, $emailTemplateVariables, $senderEmail, $reciever_email, $storeId = 1)
    {
        $sender = [
            'name' => $emailTemplateVariables['name'],
            'email' => $reciever_email,
        ];
        if ($emailTemplate == "subscription_order_email_template" || "expiration_order_email_template") {
            $senderName = $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE
            );
            $senderEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE
            );
            $sender['name'] = $senderName;
            $sender['email'] = $senderEmail;
        }
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars($emailTemplateVariables)
                ->setFromByScope($sender)
                ->addTo($reciever_email)
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * @param $membershipId
     * @param $selectedVendors
     * @return bool
     */
    public function assignMembership($membershipId, $selectedVendors)
    {
        if (count($selectedVendors) > 0) {
            $membershipModel = $this->membershipFactory->create();
            $subscription = $membershipModel->load($membershipId)->getData();
            if (isset($subscription['id'])) {
                $subscriptionId = $subscription['id'];
                $p_duration = $subscription['duration'];
                $name = $subscription['name'];
                $category = $subscription['category_ids'];
                $limit = $subscription['product_limit'];
                $price = $subscription['price'];
                $specialPrice = $subscription['special_price'];
                $currentTimestamp = $this->dateTime->timestamp(time());
                $date = date('Y-m-d', $currentTimestamp);
                $duration = "+ $p_duration months";
                $newDate = date('Y-m-d', strtotime($duration, strtotime($date)));
                foreach ($selectedVendors as $Vendor) {
                    $existing = $this->getExistingSubcription($Vendor);
                    if (in_array($membershipId, $existing)) {
                        continue;
                    }
                    $VendorDetails = $this->vendorFactory->create()->load($Vendor);
                    $customer_email = $VendorDetails->getEmail();
                    $model = $this->subscriptionFactory->create();
                    $model->setData('vendor_id', $Vendor);
                    $model->setData('subscription_id', $subscriptionId);
                    $model->setData('store', $this->storeManager->getStore(null)->getStoreId());
                    $model->setData('website_id', $subscription['website_id']);
                    $model->setData('status', \Ced\CsMembership\Model\Status::STATUS_RUNNING);
                    $model->setData('order_id', 'by admin');
                    $model->setData('start_date', $date);
                    $model->setData('end_date', $newDate);
                    $model->setData('customer_email', $customer_email);
                    $model->setData('payment_name', 'by admin');
                    $model->setData('transaction_id', 'by admin');
                    $model->setData('name', $name);
                    $model->setData('duration', $p_duration);
                    $model->setData('category_ids', $category);
                    $model->setData('product_limit', $limit);
                    $model->setData('auction_limit', $subscription['auction_limit']);
                    $model->setData('private_auction', $subscription['private_auction']);
                    $model->setData('public_auction', $subscription['public_auction']);
                    $model->setData('auction_fee', $subscription['auction_fee']);
                    $model->setData('commission', $subscription['commission']);
                    $model->setData('price', $price);
                    $model->setData('special_price', $specialPrice);
                    try {
                        $this->subscriptionResource->save($model);
                    } catch (AlreadyExistsException $e) {

                    } catch (\Exception $e) {

                    }
                }
            }
            return true;
        }
    }

    /**
     * @param $vendorId
     * @return mixed
     */
    public function getExistingSubcription($vendorId)
    {
        return $this->subscriptionCollection->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', "running")
            ->addFieldToSelect('subscription_id')
            ->addFieldToSelect('auction_limit')
            ->addFieldToSelect('auction_fee')
            ->addFieldToSelect('product_limit')
            ->addFieldToSelect('private_auction')
            ->addFieldToSelect('public_auction')
            ->addFieldToSelect('name')
            ->addFieldToSelect('commission')
            ->getData();
    }

    /**
     * Get product id by membership id
     *
     * @param $id
     * @return mixed
     */
    public function getProductIdByMembershipId($id)
    {
        // return $id;
        $ProductId = $this->membershipFactory->create()->load($id)->getProductId();
        if ($ProductId) {
            return $ProductId;
        }
    }

    /**
     * @param $order
     */
    public function setSubscription($order)
    {

        if ($this->_customerSession->getVendorId()) {
            $id = $order->getId();
            $increment_id = $order->getIncrementId();
            $orderDetails = $this->orderFactory->create()->load($id);
            $items = $order->getAllItems();
            $productId = '';
            foreach ($items as $item):
                $productId = $item->getProductId();
            endforeach;
            $vendorId = $this->_customerSession->getVendorId();
            if ($this->_customerSession->isLoggedIn()) {
                $customer = $this->_customerSession->getCustomer();
                $customer_email = $customer->getEmail();
            }
            $model = $this->subscriptionFactory->create();
            $subscription = $this->getSubscriptionByProduct($productId);
            if (isset($subscription['id'])) {
                $subscriptionId = $subscription['id'];
                $payment_method_title = $order->getPayment()->getMethodInstance()->getTitle();
                $transactionId = $order->getPayment()->getTransactionId();
                $p_duration = $subscription['duration'];
                $name = $subscription['name'];
                $category = $subscription['category_ids'];
                $limit = $subscription['product_limit'];
                $auction_limit = $subscription['auction_limit'];
                $price = $subscription['price'];
                $specialPrice = $subscription['special_price'];
                $currentTimestamp = $this->dateTime->timestamp(time());
                $date = date('Y-m-d', $currentTimestamp);
                $duration = "+ $p_duration months";
                $newDate = date('Y-m-d', strtotime($duration, strtotime($date)));
                if ($subscriptionId > 0 && $subscriptionId != "") {
                    $model->setData('vendor_id', $vendorId);
                    $model->setData('subscription_id', $subscriptionId);
                    $model->setData('store', $this->storeManager->getStore(null)->getId());
                    $model->setData('status', \Ced\CsMembership\Model\Status::STATUS_RUNNING);
                    $model->setData('order_id', $increment_id);
                    $model->setData('start_date', $date);
                    $model->setData('end_date', $newDate);
                    $model->setData('customer_email', $customer_email);
                    $model->setData('payment_name', $payment_method_title);
                    $model->setData('transaction_id', $transactionId);
                    $model->setData('name', $name);
                    $model->setData('duration', $p_duration);
                    $model->setData('category_ids', $category);
                    $model->setData('product_limit', $limit);
                    $model->setData('auction_limit', $auction_limit);
                    $model->setData('private_auction', $subscription['private_auction']);
                    $model->setData('public_auction', $subscription['public_auction']);
                    $model->setData('auction_fee', $subscription['auction_fee']);
                    $model->setData('commission', $subscription['commission']);
                    $model->setData('price', $price);
                    $model->setData('special_price', $specialPrice);
                    $model->setData('website_id', $subscription['website_id']);
                    try {
                        $this->subscriptionResource->save($model);
                        $this->verifySellerSubscription($vendorId);
                        $this->verifySellerBuyerSubscription($order->getId());
                        $qtyModel = $this->membershipFactory->create();
                        $this->membershipResource->load($qtyModel, $subscriptionId, "id");
                        //$qtyModel = $this->membershipFactory->create()->load($subscriptionId);
                        $prvqty = $qtyModel->getQty();
                        $newqty = $prvqty - 1;
                        $qtyModel->setQty($newqty);
                        $this->membershipResource->save($qtyModel);
                        // $qtyModel->save();
                        $items = $order->getAllItems();
                        $this->sendsubscriptionMail($increment_id, $customer_email, $name, $p_duration, $category, $limit, $newDate);
                        try {
                            if (!$order->canInvoice()) {
                                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create invoice.'));
                            }
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            if (!$invoice->getTotalQty()) {
                                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice without products.'));
                            }
                            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                            $invoice->register();
                            $transactionSave = $this->_transaction
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                            $transactionSave->save();
                        } catch (\Exception $e) {
                            //$this->messageManager->addErrorMessage(__($e->getMessage()));
                        }

                        $baseUrl = $storeManager->getStore()->getBaseUrl();

                        $connection = $this->resource->getConnection();
                        $action = $baseUrl . 'subscription/membership/index';
                        $this->messageManager->addSuccessMessage('You have subscribed this package successfully');

                        $sql = "INSERT INTO `ced_csmarketplace_notification`
                                (`action`, `title`, `itag`, `vendor_id`, `reference_id`, `status`)
                                VALUES
                                (:action, 'You have subscribed to package successfully.', '', $vendor_id, '680', '1')";

                        $bind = [
                            'action' => $action,
                            'vendor_id' => (int)$vendorId
                        ];

                        $connection->query($sql, $bind);

                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__($e->getMessage()));
                    }
                }
            }
        }
    }

    public function verifySellerSubscription($vendorId)
    {
        $collections = $this->subscriptionCollection->create()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToSelect('id')
            ->addFieldToSelect('subscription_id')
            ->setOrder('id', 'desc');
        $k = 1;
        if ($collections->count() > 1) {
            foreach ($collections as $collection) {
                if ($k == 1) {
                    //echo nothing to do
                } else {
                    $collection->setStatus(\Ced\CsMembership\Model\Status::STATUS_EXPIRED);
                    $collection->save();
                }
                $k++;
            }
        }
    }
    public function verifySellerBuyerSubscription($orderId)
    {
        $existing_subcription = $this->buyerMembershipHelper->getExistingSubcription($this->_customerSession->getId());
        if (empty($existing_subcription)) {
            $this->checkoutSession->setMembershipProductid(605);
            $this->buyerMembershipHelper->setSubscription($orderId, true);
        }
    }
    public function getAuctionTypes()
    {
        $allow_public_auction = $this->scopeConfig->getValue('auction_entry_1/standard/allow_public_auction', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allow_private_auction = $this->scopeConfig->getValue('auction_entry_1/standard/allow_private_auction', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $existing_subscription = $this->getExistingSubcription($this->_customerSession->getVendorId());
        $arr = [];
        // if( !empty( $existing_subscription ) ){
        if ($allow_public_auction) {
            // if( isset( $existing_subscription[0]['public_auction'] ) && $existing_subscription[0]['public_auction'] ){
            $arr[] = ['value' => '0', 'label' => 'Public Auction'];
            // }
        }
        if ($allow_private_auction) {
            // if( isset( $existing_subscription[0]['private_auction'] ) && $existing_subscription[0]['private_auction'] ){
            $arr[] = ['value' => '1','label' => 'Private Auction'];
            // }
        }
        // }

        return $arr;
    }
    public function getRemainingAuction()
    {
        $auctionCollection = $this->auctionFactory->create()->getCollection();
        $auctionCollection->addFieldToFilter('vendor_id', $this->_customerSession->getVendorId());

        return $auctionCollection->getSize();
    }

    /**
     * @return mixed
     */
    public function getMembershipPlans()
    {
        $data = $this->membershipCollectionFactory->create()
            ->addFieldToFilter('status', self::STATUS_ENABLED);//->addFieldToFilter('group_type',trim(strtolower($groupCode)))
        return $data;
    }
}
