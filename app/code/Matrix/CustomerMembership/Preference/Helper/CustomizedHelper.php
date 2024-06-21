<?php

namespace Matrix\CustomerMembership\Preference\Helper;

use Ced\CustomerMembership\Helper\Data;

class CustomizedHelper extends Data
{
    protected $subscriptionCollectionFactory;
    protected $subscriptionFactory;
    protected $membershipFactory;
    protected $quoteFactory;
    protected $_localeDate;
    protected $orderFactory;
    protected $subscriptionModelFactory;
    protected $membershipCollectionFactory;
    protected $invoiceService;
    protected $customerFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Ced\CustomerMembership\Model\ResourceModel\Subscription\CollectionFactory $subscriptionCollectionFactory,
        \Ced\CustomerMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CustomerMembership\Model\MembershipFactory $membershipFactory,
        \Ced\RequestToQuote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $_localeDate,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ced\CustomerMembership\Model\SubscriptionFactory $subscriptionModelFactory,
        \Ced\CustomerMembership\Model\ResourceModel\Membership\CollectionFactory $membershipCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $inlineTranslation,
            $customerSession,
            $checkoutSession,
            $date,
            $transaction,
            $transportBuilder
        );
        $this->_localeDate = $_localeDate;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->membershipFactory = $membershipFactory;
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->subscriptionModelFactory = $subscriptionModelFactory;
        $this->membershipCollectionFactory = $membershipCollectionFactory;
        $this->invoiceService = $invoiceService;
        $this->customerFactory = $customerFactory;
    }

    public function getExistingSubcription($customerId)
    {
        return $this->subscriptionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', "running")
            ->addFieldToSelect('rfq_limit')
            ->addFieldToSelect('noncatrfq_limit')
            ->addFieldToSelect('noncatrfq_fee')
            ->addFieldToSelect('membership_id')
            ->getData();
    }

    public function subscriptionStatus()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $existing_subscription = $this->getExistingSubcription($customerId);
        return !empty($existing_subscription);
    }

    public function setSubscription($orderid, $auto = false)
    {
        $order = $this->orderFactory->create()->load($orderid);
        $product_ids = [];
        foreach ($order->getAllVisibleItems() as $items) {
            $product_ids[] = $items->getProductId();
        }
        if ($auto) {
            $product_ids[] = $this->_checkoutSession->getMembershipProductid();
        }

        if ($this->_customerSession->isLoggedIn() && in_array($this->_checkoutSession->getMembershipProductid(), $product_ids)) {
            try {
                $subscription = $this->subscriptionModelFactory->create();
                $membershipdata = $this->membershipCollectionFactory->create()
                    ->addFieldToFilter('product_id', $this->_checkoutSession->getMembershipProductid())
                    ->getFirstItem();
                $startdate = $this->date->gmtDate();
                $pdate = $membershipdata->getDuration();
                $enddate = date('Y-m-d', strtotime("+$pdate days"));
                $subscription->setMembershipId($membershipdata->getId());
                $subscription->setPlanName($membershipdata->getPlanName());
                $subscription->setCustomerId($order->getCustomerId());
                $subscription->setCustomerEmail($order->getCustomeremail());
                $subscription->setOrderId($order->getIncrementId());
                $subscription->setPaymentName($order->getPayment()->getMethodInstance()->getTitle());
                $subscription->setPackagePrice($membershipdata->getPackagePrice());
                $subscription->setPackageSpecialprice($membershipdata->getPackageSpecialprice());
                $subscription->setOrderDiscount($membershipdata->getOrderDiscount());
                $subscription->setDuration($membershipdata->getDuration());
                $subscription->setStatus('running');
                $subscription->setDescription($membershipdata->getDescription());
                $subscription->setWebsite($this->_storeManager->getStore()->getWebsiteId());
                $subscription->setStartDate($startdate);
                $subscription->setEndDate($enddate);
                $subscription->setRfqLimit($membershipdata->getRfqLimit());
                $subscription->setNoncatrfqLimit($membershipdata->getNoncatrfqLimit());
                $subscription->setNoncatrfqFee($membershipdata->getNoncatrfqFee());
                $subscription->save();

                $this->verifyBuyerSubscription($order->getCustomerId());

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

                $this->sendConfirmationEmail($order->getCustomerId(), $membershipdata->getPlanName(), $enddate, $membershipdata->getOrderDiscount());
            } catch (\Exception $e) {
                // Log the exception
            }
        }
        $this->_checkoutSession->unsMembershipProductid();
    }

    public function sendConfirmationEmail($customerid, $planname, $duration, $discount)
    {
        $modeldata = $this->customerFactory->create()->load($customerid);

        $emailvariables['customername'] = $modeldata->getFirstname();
        $emailvariables['storename'] = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailvariables['duration'] = $this->formatDate($duration, \IntlDateFormatter::MEDIUM);
        $emailvariables['planname'] = $planname;
        $emailvariables['discount'] = $discount;

        $this->_template = 'ced_customermembership_confirmation_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFromByScope([
                'name' => $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ])
            ->addTo($modeldata->getEmail());

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            // Log the exception
        }
    }

    public function verifyBuyerSubscription($customerId)
    {
        $collections = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToSelect('id')
            ->addFieldToSelect('membership_id')
            ->setOrder('id', 'desc');

        $k = 1;
        if ($collections->count() > 1) {
            foreach ($collections as $collection) {
                if ($k > 1) {
                    $model = $this->subscriptionFactory->create()->load($collection->getId());
                    $model->setStatus('expired');
                    $model->save();
                }
                $k++;
            }
        }
    }

    public function sendExpirationEmail($subcription)
    {
        $modeldata = $this->customerFactory->create()->load($subcription->getCustomerId());

        $emailvariables['customername'] = $modeldata->getFirstName();
        $emailvariables['storename'] = $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailvariables['planname'] = $subcription->getPlanName();

        $this->_template = 'ced_customermembership_expiration_email';
        $this->_inlineTranslation->suspend();
        $this->_transportBuilder->setTemplateIdentifier($this->_template)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailvariables)
            ->setFrom([
                'name' => $this->scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            ])
            ->addSubject('Membership Expiration')
            ->addTo($modeldata->getEmail(), $modeldata->getFirstName());

        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            // Log the exception
        }
    }

    public function getProductIdByMembershipId($id)
    {
        return $this->membershipFactory->create()->load($id)->getProductId();
    }

    public function getRemainingRfq()
    {
        $currentStoreId = $this->_storeManager->getStore()->getId();
        $collections = $this->quoteFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $this->_customerSession->getCustomerId())
            ->addFieldToFilter('store_id', $currentStoreId);

        return $collections->getSize();
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
}
