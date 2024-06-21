<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CustomerMembership\Helper;

use Ced\CustomerMembership\Model\MembershipFactory;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected $_storeManager;
    protected $_inlineTranslation;
    protected $_transaction;
    protected $_transportBuilder;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_date;
    protected $_membershipFactory;
    protected $_subscriptionFactory;
    protected $_orderModel;
    protected $_customerFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        DateTime $date,
        Transaction $transaction,
        TransportBuilder $transportBuilder,
        MembershipFactory $membershipFactory,
        SubscriptionFactory $subscriptionFactory,
        Order $orderModel,
        CustomerFactory $customerFactory,
        InvoiceService $invoiceService,
        ManagerInterface $messageManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_date = $date;
        $this->_transaction = $transaction;
        $this->_transportBuilder = $transportBuilder;
        $this->_membershipFactory = $membershipFactory;
        $this->_subscriptionFactory = $subscriptionFactory;
        $this->_orderModel = $orderModel;
        $this->_customerFactory = $customerFactory;
        $this->invoiceService = $invoiceService;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue('ced_membership/general/ced_customermembership', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMembershipPlanCount()
    {
        return $this->_membershipFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', '1')
            ->addFieldToFilter('website', $this->_storeManager->getStore()->getWebsiteId())
            ->count();
    }

    public function subscriptionStatus()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $membershipData = $this->_subscriptionFactory->create()->getCollection()->addFieldToFilter('customer_id', $customerId)->getFirstItem();
        if ($this->_date->gmtDate() > $membershipData->getEndDate()) {
            $membershipData = null;
        }
        return $membershipData;
    }

    public function setSubscription($orderId)
    {
        $order = $this->_orderModel->load($orderId);
        $productIds = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        if ($this->_customerSession->isLoggedIn() && in_array($this->_checkoutSession->getMembershipProductid(), $productIds)) {
            try {
                $subscription = $this->_subscriptionFactory->create();
                $membershipData = $this->_membershipFactory->create()->getCollection()->addFieldToFilter('product_id', $this->_checkoutSession->getMembershipProductid())->getFirstItem();
                $startDate = $this->_date->gmtDate();
                $pDate = $membershipData->getDuration();
                $endDate = date('Y-m-d', strtotime("+$pDate days"));
                $subscription->setMembershipId($membershipData->getId());
                $subscription->setPlanName($membershipData->getPlanName());
                $subscription->setCustomerId($order->getCustomerId());
                $subscription->setCustomerEmail($order->getCustomeremail());
                $subscription->setOrderId($order->getIncrementId());
                $subscription->setPaymentName($order->getPayment()->getMethodInstance()->getTitle());
                $subscription->setPackagePrice($membershipData->getPackagePrice());
                $subscription->setPackageSpecialprice($membershipData->getPackageSpecialprice());
                $subscription->setOrderDiscount($membershipData->getOrderDiscount());
                $subscription->setDuration($membershipData->getDuration());
                $subscription->setStatus('running');
                $subscription->setDescription($membershipData->getDescription());
                $subscription->setWebsite($this->_storeManager->getStore()->getWebsiteId());
                $subscription->setStartDate($startDate);
                $subscription->setEndDate($endDate);
                $subscription->save();

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

                //$this->sendConfirmationEmail($order->getCustomerId(), $membershipData->getPlanName(), $endDate, $membershipData->getOrderDiscount());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_checkoutSession->unsMembershipProductid();
    }

    public function sendExpirationEmail($subscription)
    {
        $customerData = $this->_customerFactory->create()->load($subscription->getCustomerId());

        $emailVariables['customername'] = $customerData->getFirstName();
        $emailVariables['storename'] = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailVariables['planname'] = $subscription->getPlanName();

        $this->_template = 'ced_customermembership_expiration_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($emailVariables)
            ->setFrom([
                'name' => $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ])
            ->addSubject('Membership Expiration')
            ->addTo($customerData->getEmail(), $customerData->getFirstName());

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function sendConfirmationEmail($customerId, $planName, $duration, $discount)
    {
        $customerData = $this->_customerFactory->create()->load($customerId);

        $emailVariables['customername'] = $customerData->getFirstName();
        $emailVariables['storename'] = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailVariables['duration'] = $this->formatDate($duration, \IntlDateFormatter::MEDIUM);
        $emailVariables['planname'] = $planName;
        $emailVariables['discount'] = $discount;

        $this->_template = 'ced_customermembership_confirmation_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($emailVariables)
            ->setFrom([
                'name' => $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ])
            ->addSubject('Membership Subscription')
            ->addTo($customerData->getEmail(), $customerData->getFirstName());

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function subscriptionPageMessage()
    {
        return $this->scopeConfig->getValue('ced_membership/cmembership/cmembership_description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }
    public function getCustomerisLogin()
    {
        return $this->_customerSession->isLoggedIn();
    }
}
