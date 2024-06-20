<?php
namespace Matrix\NoncatalogueRfq\Helper;

use Ced\CustomerMembership\Helper\Data as customerMembershipHelper;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Swatches\Helper\Data as SwatchData;
use Matrix\NoncatalogueRfq\Model\RfqPo;
use Matrix\NoncatalogueRfq\Model\RfqProductFactory;

class Data extends AbstractHelper
{
    const NONCATALOG_RFQ_TYPE_PUBLIC = 1;
    const NONCATALOG_RFQ_TYPE_PRIVATE = 2;

    const     SMS_CONFIG_VENDOR_NONCATALOG_RFQ = 'noncatalogrfq_configuration/smsnotification/vendor_new_noncatalogrfq_sms';
    const     SMS_CONFIG_VENDOR_PUBLIC_NONCATALOG_RFQ = 'noncatalogrfq_configuration/smsnotification/vendor_new_publivnoncatalogrfq_sms';
    const     SMS_CONFIG_ADMIN_NONCATALOG_RFQ = 'noncatalogrfq_configuration/smsnotification/admin_noncatalogrfq_sms';

    //const UOM_OPTION_OTHER_ID = 5492; // Live = 5534,  Local = 5492
    const UOM_OPTION_OTHER_ID = 5534;

    protected $_admin_send_email = 'admin_noncatalogrfq_submit_email_template';

    /* @var \Magento\Framework\App\Http\Context
    */
    private $httpContext;

    protected $eavConfig;

    protected $swatchHelper;

    protected $storeManager;

    protected $_countryCollectionFactory;

    protected $customer;

    protected $_customerFactory;

    protected $_addressFactory;

    protected $context;

    protected $_scopeConfig;

    protected $_inlineTranslation;

    protected $_transportBuilder;

    protected $rfqProductFactory;

    /**
     * @var Po
     */
    protected $_po;

    protected $vendorFactory;

    protected $subscriptionFactory;

    protected $subscriptionCollectionFactory;

    protected $rfqcollectionFactory;

    protected $customerMembershipHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Session $customer,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq\CollectionFactory $rfqcollectionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        RfqPo $po,
        RfqProductFactory $rfqProductFactory,
        SubscriptionFactory $subscriptionFactory,
        \Ced\CustomerMembership\Model\ResourceModel\Subscription\CollectionFactory $subscriptionCollectionFactory,
        customerMembershipHelper $customerMembershipHelper,
        SwatchData $swatchHelper
    ) {
        parent::__construct($context);
        $this->httpContext = $httpContext;
        $this->_eavConfig = $eavConfig;
        $this->swatchHelper = $swatchHelper;
        $this->_po = $po;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->date = $date;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->vendorFactory = $vendorFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->rfqcollectionFactory = $rfqcollectionFactory;
        $this->customerMembershipHelper = $customerMembershipHelper;
        $this->rfqProductFactory = $rfqProductFactory;
        $this->context = $context;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path, $storeId = 1)
    {
        //return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getConfigValue('noncatalogrfq_configuration/active/enable');
    }

    public function isLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    public function getRFQuoteTypes()
    {
        $arrRfqTypes = [
        self::NONCATALOG_RFQ_TYPE_PUBLIC => 'Public Non-catalog RFQ'
        ,self::NONCATALOG_RFQ_TYPE_PRIVATE => 'Private  Non-catalog RFQ'];
        return $arrRfqTypes;
    }

    public function customerInfo()
    {

        $customerData =  [];
        if (!$this->isLoggedIn()) {
            return null;
        }

        $customer = $this->customer;
        $customerData['customerName'] =  $customer->getName();
        $customerData['customerId'] = $customer->getId();
        $customerData = $customer->getData();
        $customer = $this->_customerFactory->create()->load($customer->getData('customer_id'));    //insert customer id
        $fullName = $customer->getData('firstname') . ' ' . $customer->getData('middlename') . ' ' . $customer->getData('lastname');
        //Company Name From Billing Adress
        //get customer model before you can get its address data

        //billing
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->_addressFactory->create()->load($billingAddressId);

        //$customerData ['company'] = $customer->getBillingAddress()->getCompany();
        $companyName = $billingAddress->getData('company');
        $customerData['companyName'] = ($companyName!='') ? $companyName : '';
        $customerData['customerName'] = $fullName;
        return $customerData;
    }

    public function getSwatchesByOptionsId($optionIds)
    {
        return $this->swatchHelper->getSwatchesByOptionsId($optionIds);
    }

    public function getSourceTypes()
    {
        $attributeCode = "noncatrfq_sourcingtype";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getSourcePurpose()
    {
        $attributeCode = "noncatrfq_sourcingpurpose";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getSourcePurposeJson()
    {
        return json_encode($this->getSourcePurpose());
    }

    public function getTradeTerms()
    {
        $attributeCode = "noncatrfq_tradeterms";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getTradeTermsJson()
    {
        return json_encode($this->getTradeTerms());
    }

    public function getUnitsofMeasures()
    {
        $attributeCode = "noncatrfq_uom";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();

        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getUomOptions($rfqProductId = 0)
    {
        $attributeCode = "noncatrfq_uom";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[$option['value']] = $option['label'];
            }
        }

        if ($rfqProductId>0) {
            $rfqProduct = $this->rfqProductFactory->create()->load($rfqProductId);
            if ($rfqProduct->getRfqProductId()) {
                $arr[self::UOM_OPTION_OTHER_ID] = $rfqProduct->getUmoOther();
            }
        }
        return $arr;
    }

    public function getSupplierCertifications()
    {
        /*$attributeCode = "noncatrfq_suppliercertifications";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        return $options;*/
        $attributeCode = "noncatrfq_suppliercertifications";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getCertifications()
    {
        $attributeCode = "noncatrfq_suppliercertifications";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        $swatchUrl = $this->getMediaUrl() . 'attribute/swatch/';
        foreach ($options as $option) {
            if ($option['value'] > 0) {

                $swatchImage =$swatchUrl . $option['value'];
                //$option['style']="background-image:url('".$swatchImage."')" ;
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getCertificationsJson()
    {
        return json_encode($this->getCertifications());
    }

    public function getCertificationsOptionSwatchs()
    {
        $certoptionIds  =  [];
        $swatchOption =  [];
        $supplierCertifications =  $this->getSupplierCertifications();
        if (count($supplierCertifications)) {
            foreach ($supplierCertifications as $indec => $certificateOption) {
                $certoptionIds[]= $certificateOption['value'];
            }
            $swatchOption =  $this->getSwatchesByOptionsId($certoptionIds);
            foreach ($supplierCertifications as $index => $certificateOption) {
                $swatchOption[$certificateOption['value']]['label'] = $certificateOption['label'];
            }
        }
        return $swatchOption;
    }

    public function getPaymentTerms()
    {
        $attributeCode = "noncatrfq_paymentterm";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getShippingMethods()
    {
        $attributeCode = "noncatrfq_shippingmethods";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
        foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] = $option;
            }
        }
        return $arr;
    }

    public function getMediaUrl()
    {
        $currentStore = $this->storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->context->getScopeConfig()->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Retrieve list of countries in array option
     *
     * @return array
     */
    public function getCountries()
    {
        return $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                   ->toOptionArray();
    }
    public function obfuscate_email($email)
    {
        $em   = explode("@", $email);
        $em_part = explode(".", end($em));
        $name = implode('@', array_slice($em, 0, count($em)-1));
        $len  = floor(strlen($name)/2);
        return substr($name, 0, $len) . str_repeat('*', $len) . "@" . "***" . end($em_part);
    }

    public function obfuscate($str, $clear = 4)
    {
        return substr($str, 0, $clear)
         . preg_replace('/[^\s]/', 'X', substr($str, $clear));
    }

    /**
     * @param $template
     * @param $tempate_variables
     * @param $customer_email
     * @return \Magento\Framework\Phrase
     */
    public function sendAdminEmail($template, $tempate_variables, $customer_email)
    {
        $template = 'admin_' . $template;
        $senderName = $this->getConfigValue('trans_email/ident_general/name');
        $senderEmail = $this->getConfigValue('trans_email/ident_general/email');
        $reciever_email = $this->getConfigValue('noncatalogrfq_configuration/active/admin_mail');
        //echo "reciever_email=".$reciever_email;
        if (!$reciever_email) {
            return false;
        }
        $sender = [
                    'name' => $senderName,
                    'email' => $senderEmail,
                ];
        try {
            $tempate_variables['customer_email'] = $customer_email;
            $transport = $this->_transportBuilder->setTemplateIdentifier($template)
              ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()])
              ->setTemplateVars($tempate_variables)
              ->setFrom($sender)
              ->addTo($reciever_email)
              ->getTransport();
            $transport->sendMessage();
            return true;

        } catch (\Exception $e) {
            //return __( 'Unable to send mail.' );
            //echo $e->getMessage();
            return false;
        }
    }

    public function getVendorByCustomerId($vendorId)
    {
        $collection = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('entity_id', ['ed'=>$vendorId])
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('customer_id')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('rfqseller_isvisible')
            ->addAttributeToSelect('reason');
        if ($collection->getSize()>0) {
            $item =  $collection->getFirstItem();
            return $this->_customerFactory->create()->load($item->getCustomerId());

        } else {
            return null;
        }
    }

    public function getVendorByCustomerEntityId($customerId)
    {
        $collection = $this->vendorFactory->create()->getCollection()
            ->addAttributeToFilter('customer_id', ['ed'=>$customerId])
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group')
            ->addAttributeToSelect('status');

        if ($collection->getSize()>0) {
            $item =  $collection->getFirstItem();
            //return $this->_customerFactory->create()->load($item->getCustomerId());
            return $item;

        } else {
            return null;
        }
    }

    public function showSellerInfo($vendorId, $poId = 0)
    {
        if ($poId<=0) {
            return false;
        }
        $vendorCustomer = $this->getVendorByCustomerId($vendorId);
        if (!isset($vendorCustomer)) {
            return false;
        }
        $po = $this->_po->load($poId);
        if (!isset($po)) {
            return false;
        }
        $isShow = false;
        //visibility periority 1
        $is_feespaid  = ($po->getData('is_feespaid')==1 && $po->getData('rfq_fees')>0) ? true : false;
        $globalSellerVisibiltyConfig = $this->getConfigValue('noncatalogrfq_configuration/active/allow_seller_visibilty');
        $rfqseller_isvisible =  $vendorCustomer->getData('rfqseller_isvisible');

        if ($is_feespaid && $globalSellerVisibiltyConfig && $rfqseller_isvisible) {
            $isShow = true;
        } elseif ($is_feespaid && $globalSellerVisibiltyConfig==0 && $rfqseller_isvisible) {
            $isShow = true;
        } elseif ($is_feespaid && $globalSellerVisibiltyConfig==1 && $rfqseller_isvisible==0) {
            $isShow = false;
        } else {
            $isShow = false;
        }

        /*if($globalSellerVisibiltyConfig==1){
            $isShow = true;
        } else {
            $isShow = false;
        }

        //visibility periority 3
        $rfqseller_isvisible =  $vendorCustomer->getData('rfqseller_isvisible');
        if($isShow && $rfqseller_isvisible==1){
            $isShow = true;
        } else if($rfqseller_isvisible==0){
            $isShow = false;

        } else {
            $isShow = false;
        }*/

        return $isShow;
    }

    public function getCustomerInfo($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);    //insert customer id
        return ($customer) ? $customer : null;
    }

    public function showBuyerInfo($customerId, $poId = 0)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        if (!$customer || $customer->getId()<=0) {
            return false;
        }
        $isShow = false;
        $globalBuyerVisibiltyConfig = $this->getConfigValue('noncatalogrfq_configuration/active/allow_buyer_visibilty');
        $rfqbuyer_isvisible =  $customer->getData('rfqbuyer_isvisible');
        //$isShow  = (isset($rfqbuyer_isvisible) && $rfqbuyer_isvisible)? true : false;
        if ($rfqbuyer_isvisible ==1 && $globalBuyerVisibiltyConfig==1) {
            $isShow = true;
        } elseif ($rfqbuyer_isvisible ==1 && $globalBuyerVisibiltyConfig==0) {
            $isShow = true;
        } elseif ($rfqbuyer_isvisible ==0 && $globalBuyerVisibiltyConfig==1) {
            $isShow = false;
        } else {
            $isShow = false;
        }
        return $isShow;
    }

    public function isSubscribedMembership()
    {

        $membershipCollection =   $this->subscriptionFactory->create()->getCollection()->addFieldToFilter('customer_id', $this->customer->getCustomerId());
        return (count($membershipCollection->getData())>0) ? true : false;
    }

    public function getMembershipSubscriptionbyCustomerId($customerId)
    {
        $status = "running";
        $activeSubscription = null;
        $customerId = $this->customer->getCustomer()->getId();
        $subscriptionArr = $this->customerMembershipHelper->getExistingSubcription($customerId);
        if (is_array($subscriptionArr) && count($subscriptionArr)) {
            $subscriptionArr = $subscriptionArr[0];
            $activeSubscription = new \Magento\Framework\DataObject();
            $activeSubscription->setData($subscriptionArr);
        }

        return $activeSubscription;
        /*$now = new \DateTime();
        $cur_time   =  date('Y-m-d');
        $ced_customermembership_tbl = 'ced_customermembership';
        $collection = $this->subscriptionFactory->create()->getCollection()
        ->addFieldToSelect(array('plan_name','customer_id','start_date','end_date'))
         ->addFieldToFilter('customer_id', $customerId)
         ->addFieldToFilter('status', $status);

        $collection->getSelect()->joinInner(
        array('second' => $ced_customermembership_tbl),
        'main_table.membership_id = second.id'
        );

        if($collection->getSize()<=0) return null;
        $curent_datetime = date('Y-m-d h:m:s');
        foreach($collection as $subscription){
            $end_date = $subscription->getData('end_date');
            if($end_date>$curent_datetime){
                $activeSbscription = $subscription;
            }
        }*/
    }

    public function isAllowNewRfq($customerId)
    {
        $isAllow = false;
        $activeSubscription = $this->getMembershipSubscriptionbyCustomerId($customerId);
        if ($activeSubscription && $activeSubscription->getMembershipId()) {
            $noncatrfq_limit = $activeSubscription->getData('noncatrfq_limit');
            $quoteModel = $this->rfqcollectionFactory->create()
            ->addFieldtoFilter('customer_id', ['customer_id' => $customerId])
            ->setOrder('rfq_id', 'DESC');
            $remainRfq =  $noncatrfq_limit -  $quoteModel->count();
            $isAllow = ($remainRfq>0) ? true : false;
        } else {// Not Subscribed to any plan or Plan expperied
        }

        return $isAllow;
    }

    public function getNonMarketInviteLimit()
    {
        return (int) $this->getConfigValue('noncatalogrfq_configuration/active/maxnonmkt_vendorinvite');
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id according to store
     *
     * @return mixed
     */
    public function getEmailTemplateId($xmlPath)
    {
        $templateId = 0;
        $configPath = 'noncatalogrfq_configuration/email/' . $xmlPath;

        $template =  $this->getConfigValue($configPath, $this->getStore()->getStoreId());
        if ($template!='') {
            $templateId =  $template;
        }
        return $templateId;
    }

    /**
     * [generateTemplate description]  with template file and tempaltes variables values
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, /* here you can defile area and
                                                                                 store of template for which you prepare it */
                        'store' => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['name']);
        //->addTo($receiverInfo['email'],$receiverInfo['name']);
        return $this;
    }

    /**
     * @param $template
     * @param array $template_variables
     * @param $reciever_email
     * @return \Magento\Framework\Phrase
     */
    public function sendEmail($template, $template_variables, $reciever_email, $reciever_name = 'customer')
    {

        $temp_id = $this->getEmailTemplateId($template);
        $senderName = $this->getConfigValue('trans_email/ident_general/name');
        $senderEmail = $this->getConfigValue('trans_email/ident_general/email');
        try {
            $senderInfo = [
               'name' => $senderName,
               'email' => $senderEmail];

            $receiverInfo = [
                'name' => $reciever_name,
                 'email' => $reciever_email];

            if (isset($temp_id) && $temp_id>0) { //  transactional mail
                $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()])
                ->setTemplateVars($template_variables)
                ->setFrom($senderInfo)
                ->addTo($reciever_email)
                ->getTransport();
                $transport->sendMessage();
                return true;

                /*$this->temp_id = $temp_id;
                $this->_inlineTranslation->suspend();
                $this->generateTemplate($template_variables,$senderInfo,$receiverInfo);
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                return true;*/
            } else { //  default template mail

                $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId()])
                ->setTemplateVars($template_variables)
                ->setFrom($senderInfo)
                ->addTo($reciever_email)
                ->getTransport();
                $transport->sendMessage();
                return true;
            }
        } catch (\Exception $e) {
            //return __( 'Unable to send mail.' );
            return false;
        }
    }

    public function getSMSTemplate($configPath, $smsVariables)
    {
        if (!is_array($smsVariables) || count($smsVariables)<=0) {
            return '';
        }
        if ($configPath=='') {
            return '';
        }
        $smsContent = $this->getConfigValue($configPath);
        $smsContentupdated = $smsContent;
        foreach ($smsVariables as $key => $value) {
            $smsContentupdated = str_replace('{{' . $key . '}}', $value, $smsContentupdated);
        }
        return $smsContentupdated;
    }
}
