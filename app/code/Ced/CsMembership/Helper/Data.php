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
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Data (Helper for adding common functions used in various files)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializerInterface;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializerInterface
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $customerSession,
        \Magento\Framework\DB\Transaction $transaction,
        ManagerInterface $messageManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customerSession = $customerSession;
        $this->_transaction = $transaction;
        $this->messageManager = $messageManager;
        $this->_transportBuilder = $transportBuilder;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->membershipFactory = $membershipFactory;
        $this->dateTime = $dateTime;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->categoryFactory = $categoryFactory;
        $this->_serializerInterface = $serializerInterface;
        $this->_priceCurrency = $priceCurrency;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get categories cost
     *
     * @param $category_ids
     * @return int|mixed
     */
    public function getCategoriesCost($category_ids)
    {
        $categories = array_unique(explode(',', $category_ids));
        $perCategoryPrice = $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/category_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $specificCategoryPrice = $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/category_prices',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $specificCategoryPrice = $this->_serializerInterface->unserialize($specificCategoryPrice);
        $Price = 0;
        $groupCategory = $this->scopeConfig->getValue(
            'ced_csmarketplace/vproducts/category_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $groupAllowedCategory = $this->scopeConfig->getValue(
            'ced_csmarketplace/vproducts/category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($groupCategory == '0') {
            return $Price;
        } else {
            $freeCategories = array_unique(explode(',', $groupAllowedCategory));
        }
        $specific = [];
        if (!empty($specificCategoryPrice)) {
            foreach ($specificCategoryPrice as $key => $category) {
                if ($key == '__empty') {
                    continue;
                }
                if (in_array($category['category'], $categories)) {
                    array_push($specific, $category['category']);
                    $Price = $Price + $category['price'];
                }
            }
        }

        foreach ($categories as $category) {
            if (!in_array($category, $specific) && !in_array($category, $freeCategories)) {
                $Price = $Price + $perCategoryPrice;
            }
        }
        return $Price;
    }

    /**
     * Get Product cost
     *
     * @param $product_limit
     * @return float|int
     */
    public function getProductCost($product_limit)
    {
        $perproductCost = $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/product_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $product_limit * $perproductCost;
    }

    /**
     * Get duration cost
     *
     * @param $duration
     * @return int
     */
    public function getDurationCost($duration)
    {
        $durationCost = 0;
        $durationCosts = $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/duration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $durationCosts = $this->_serializerInterface->unserialize($durationCosts);

        foreach ($durationCosts as $key => $value) {
            if ($key == '__empty') {
                continue;
            }
            if (trim($value['duration']) == trim($duration)) {
                $durationCost = $value['price'];
            }
        }
        return $durationCost;
    }

    /**
     * Get vendor email
     *
     * @return array
     */
    public function getVendorEmail()
    {
        $options = ['' => '-- ' . __('All Vendor Subscription') . ' --'];
        $collection = $this->subscriptionFactory->create()->getCollection();
        foreach ($collection as $key => $value) {
            $options[$value->getVendorId()] = $value->getCustomerEmail();
        }
        return $options;
    }

    /**
     * Get memberships
     *
     * @return array
     */
    public function getMemberships()
    {
        $option = ['' => '-- ' . __('All Membership Users') . ' --'];
        $collection = $this->subscriptionFactory->create()->getCollection();
        foreach ($collection as $key => $value) {
            $option[$value->getSubscriptionId()] = __($value->getName());
        }
        return $option;
    }

    /**
     * Get subscribed users
     *
     * @param $packageId
     * @return mixed
     */
    public function getSubscribedUsers($packageId)
    {
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('subscription_id', $packageId)
            ->addFieldToSelect('vendor_id')
            ->getData();
        return $collection;
    }

    /**
     * Get base price
     *
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/base_membership_fee',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Assign membership
     *
     * @param $membershipId
     * @param $selectedVendors
     * @return bool
     */
    public function assignMembership($membershipId, $selectedVendors)
    {
        if (count($selectedVendors) > 0) {
            $membershipModel = $this->membershipFactory->create();
            $subscription = $membershipModel->load($membershipId)->getData();
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
                $model->setData('price', $price);
                $model->setData('special_price', $specialPrice);
                try {
                    $model->save();
                } catch (Exception $e) {
                }
            }
            return true;
        }
    }

    /**
     * Get existing subscription
     *
     * @param $vendorId
     * @return mixed
     */
    public function getExistingSubcription($vendorId)
    {
        $status = "running";
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', $status)
            ->addFieldToSelect('subscription_id')
            ->getData();
        return $collection;
    }

    /**
     * Get membership plans
     *
     * @return mixed
     */
    public function getMembershipPlans()
    {
        $data = $this->membershipFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'status',
                \Ced\CsMembership\Model\Status::STATUS_ENABLED
            );//->addFieldToFilter('group_type',trim(strtolower($groupCode)))
        return $data;
    }

    /**
     * Get product id by membership id
     *
     * @param $id
     * @return mixed
     */
    public function getProductIdByMembershipId($id)
    {
        $ProductId = $this->membershipFactory->create()->load($id)->getProductId();
        if ($ProductId) {
            return $ProductId;
        }
    }

    /**
     * Set subscription
     *
     * @param $order
     */
    public function setSubscription($order)
    {
        if ($this->_customerSession->getVendorId() || $order) {
            $id = $order->getId();
            $increment_id = $order->getIncrementId();
            $orderDetails = $this->orderFactory->create()->load($id);
            $items = $order->getAllItems();
            foreach ($items as $item):
                $productId = $item->getProductId();
            endforeach;
            $vendorId = $this->_customerSession->getVendorId();
            if ($vendorId == null || $vendorId == '') {
                $vendorData = $this->vendorFactory->create()->loadByCustomerId($order->getCustomerId());
                if ($vendorData) {
                    $vendorId = $vendorData->getEntityId();
                }
            }
            if ($this->_customerSession->isLoggedIn()) {
                $customer = $this->_customerSession->getCustomer();
                $customer_email = $customer->getEmail();
            }
            $model = $this->subscriptionFactory->create();
            $subscription = $this->getSubscriptionByProduct($productId);
            $subscriptionId = isset($subscription['id']) ? $subscription['id'] : '';
            if ($subscriptionId) {
                $payment_method_title = $order->getPayment()->getMethodInstance()->getTitle();
                $transactionId = $order->getPayment()->getTransactionId();
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
                $model->setData('vendor_id', $vendorId);
                $model->setData('subscription_id', $subscriptionId);
                $model->setData('store', $this->storeManager->getStore(null)->getId());
                $model->setData('status', \Ced\CsMembership\Model\Status::STATUS_PENDING);
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
                $model->setData('price', $price);
                $model->setData('special_price', $specialPrice);
                $model->setData('website_id', $subscription['website_id']);
                try {
                    $model->save();
                    $qtyModel = $this->membershipFactory->create()->load($subscriptionId);
                    $prvqty = $qtyModel->getQty();
                    $newqty = $prvqty - 1;
                    $qtyModel->setQty($newqty);
                    $qtyModel->save();
                    $items = $order->getAllItems();
                    $this->sendsubscriptionMail(
                        $increment_id,
                        $customer_email,
                        $name,
                        $p_duration,
                        $category,
                        $limit,
                        $newDate
                    );
                    $this->messageManager->addSuccessMessage('You have subscribed this package successfully');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
        }
    }

    /**
     * @param $productId
     * @return $data;
     */
    public function getSubscriptionByProduct($productId)
    {
        $data = $this->membershipFactory->create()
            ->getCollection()
            ->addFieldToFilter('product_id', $productId)
            ->getData();

        if (!empty($data)) {
            return $data[0];
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedCategory()
    {
        $vendorId = $this->_customerSession->getVendorId();
        $subcriptionCollection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', 'running');
        //->addFieldToFilter('store', $this->storeManager->getStore(null)->getStoreId());
        $categories = [];
        foreach ($subcriptionCollection as $key => $subcription) {
            if ($subcription->getStatus() == \Ced\CsMembership\Model\Status::STATUS_RUNNING) {
                $categoryIds = $subcription->getCategoryIds();
                $categoryJson = explode(',', $categoryIds);
                $categories = array_merge($categoryJson, $categories);
            }
        }
        return array_unique($categories);
    }

    /**
     * @param $storeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLimit($storeId)
    {

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $vendorId = $this->_customerSession->getVendorId();
        //         $productLimit = 0;
        $subcriptionCollection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendorId)
            ->addFieldToFilter('status', 'running');

        $productLimit = $this->scopeConfig->getValue('ced_vproducts/general/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        foreach ($subcriptionCollection as $key => $subcription) {

            if ($subcription->getStatus() == \Ced\CsMembership\Model\Status::STATUS_RUNNING) {
                $productLimit = $productLimit + $subcription->getProductLimit();
            }
        }
        return $productLimit;
    }

    /**
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setExpireMethod()
    {
        $product_limit = $this->getLimit($this->storeManager->getStore(null)->getStoreId());

        $perproductCost = $this->scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/product_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $product_limit * $perproductCost;
    }

    /**
     * @param $IncrementId
     * @param $customer_email
     * @param $name
     * @param $p_duration
     * @param $category
     * @param $limit
     * @param $newDate
     * @return bool
     */
    public function sendsubscriptionMail($IncrementId, $customer_email, $name, $p_duration, $category, $limit, $newDate)
    {
        $vendor = $this->vendorFactory->create()->load($this->_customerSession->getVendorId());
        if ($vendor->getId() && $vendor->getName()) {
            $customerName = $vendor->getName();
        } else {
            $customerName = $vendor->getPublicName();
        }

        $emailTemplate = 'subscription_order_email_template';
        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $emailTemplateVariables = [];
        $emailTemplateVariables['name'] = $customerName;
        $emailTemplateVariables['email'] = $customer_email;
        $emailTemplateVariables['orderid'] = $IncrementId;
        $emailTemplateVariables['package'] = $name;
        $emailTemplateVariables['duration'] = $p_duration . ' Month(s)';
        $category = explode(',', $category ?? '');
        $cathtml = '';
        foreach ($category as $cat) {
            $model = $this->categoryFactory->create()->load($cat);
            if ($model->getLevel() == '0' || $model->getLevel() == '1') {
                continue;
            }
            $cathtml = $cathtml . $model->getName() . '<br>';
        }
        $emailTemplateVariables['category'] = $cathtml;
        $emailTemplateVariables['limit'] = $limit;
        $emailTemplateVariables['expire'] = $newDate;
        $this->sendMail($emailTemplate, $emailTemplateVariables, $senderEmail, $customer_email, $storeId = 1);
    }

    /**
     * @param $subcription
     * @return bool
     */
    public function sendExpireMail($subscription)
    {
        $vendorId = $subscription->getVendorId();
        $customer_email = $subscription->getCustomerEmail();
        $vendor = $this->vendorFactory->create()->load($vendorId);
        if ($vendor->getId() && $vendor->getName()) {
            $customerName = $vendor->getName();
        } else {
            $customerName = $vendor->getPublicName();
        }

        $emailTemplate = 'expiration_order_email_template';
        $senderName = $this->scopeConfig->getValue(
            'trans_email/ident_general/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $senderEmail = $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $emailTemplateVariables = [];
        $emailTemplateVariables['name'] = $customerName;
        $emailTemplateVariables['package'] = $subscription->getName();
        $this->sendMail($emailTemplate, $emailTemplateVariables, $senderEmail, $customer_email, $storeId = 1);
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
        try {
            $sender = [
                'name' => $emailTemplateVariables['name'],
                'email' => $reciever_email,
            ];
            $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($sender)
                ->addTo($reciever_email)
                ->getTransport();
            $transport->sendMessage();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Formats the int/string to base currency
     * @param int|string $amount
     * @param int|string|null $storeId
     *
     * @return string|null
     */
    public function formatToBaseCurrency($amount, $storeId = null)
    {
        $currencyCode = $this->storeManager->getStore($storeId)->getBaseCurrencyCode();
        return $this->_priceCurrency->format($amount, false, 2, null, $currencyCode);
    }
}
