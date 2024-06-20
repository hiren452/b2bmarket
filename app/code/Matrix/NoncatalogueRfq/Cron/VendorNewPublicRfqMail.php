<?php
namespace Matrix\NoncatalogueRfq\Cron;

use Magento\Catalog\Model\CategoryFactory;

class VendorNewPublicRfqMail
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /** @var Noncatalogquote|NoncatalogRfqFactory
     */
    protected $_noncatalogquote;

    /**
     * @var RfqVenforFactory|RfqVendorFactory
     */
    protected $_rfqVendorFactory;

    protected $_vendorFactory;

    /**
     * @var NoncatalogqProduct|RfqProductFactory
     */
    protected $_noncatalogqProduct;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var Data
     */
    protected $helper;

    protected $categoryFactory;

    protected $smsnotificationHelper;

    protected $_pricingHelper;

    /**
     * CheckExpire constructor.
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory $noncatalogquote,
        \Matrix\NoncatalogueRfq\Model\RfqProductFactory $noncatalogqProduct,
        \Matrix\NoncatalogueRfq\Model\RfqVendorFactory $rfqVendorFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Matrix\NoncatalogueRfq\Helper\Data $helper,
        \Ced\CsTwiliosmsnotification\Helper\Data $smsnotificationHelper,
        CategoryFactory $categoryFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Matrix\NoncatalogueRfq\Logger\Logger $logger
    ) {

        $this->urlInterface = $urlInterface;
        $this->_noncatalogquote = $noncatalogquote;
        $this->_noncatalogqProduct = $noncatalogqProduct;
        $this->_rfqVendorFactory = $rfqVendorFactory;
        $this->_vendorFactory = $vendorFactory;
        $this->quoteStatus = $quoteStatus;
        $this->categoryFactory = $categoryFactory;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        $this->smsnotificationHelper =  $smsnotificationHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_logger = $logger;
    }

    public function getParentCategory($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        // Parent Categories
        //$parentCategories = $category->getParentCategories();
        $parentCategoriesids = $category->getParentIds();

        $parentCategoriesids =  array_values($parentCategoriesids);
        return $parentCategoriesids;
    }

    public function execute()
    {
        try {
            $cur_time = date('Y-m-d');
            $collection = $this->_noncatalogquote->create()
            ->getCollection()
            ->addFieldToFilter('rfq_type', $this->helper::NONCATALOG_RFQ_TYPE_PUBLIC)
            ->addFieldToFilter('is_publicmail_send', ['eq'=>0])
            ->addFieldToFilter('status', ['neq'=>\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED]);
            //->addFieldToFilter('respons_date', array('lt' => date("Y-m-d")));
            if ($collection->getSize()) {
                $uomOptions = $this->helper->getUomOptions();
                $siteUrl = $this->urlInterface->getUrl();
                foreach ($collection as $rfq) {
                    $item_info =  [];
                    $totals =  [];
                    $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/', ['id'=>$rfq->getData('rfq_id')]);

                    $rfqdetailUrl = $this->urlInterface->getUrl('csmarketplace/account/login', ['referer' => base64_encode($rfqUrl)]);
                    $customerName = $rfq->getData('name');
                    $company_name = $rfq->getData('company_name');
                    $label = $this->quoteStatus->getOptionText($rfq->getStatus());
                    $totals['subtotal'] = $this->_pricingHelper->currency($rfq->getRfqTotalPrice(), true, false);
                    $totals['grandtotal'] =  $this->_pricingHelper->currency($rfq->getRfqTotalPrice(), true, false);
                    $nonCatalogQuoteProductCollection = $this->_noncatalogqProduct->create()
                    ->getCollection()
                    ->addFieldToFilter('rfq_id', $rfq->getData('rfq_id'));
                    //$rfqCategoryIds =  array();
                    $rfqCategoryIds[] =  $rfq->getCategoryIds();
                    if (is_array($rfqCategoryIds) && count($rfqCategoryIds)) {
                        if ($nonCatalogQuoteProductCollection->getSize()) {
                            foreach ($nonCatalogQuoteProductCollection as $rfqProduct) {
                                $uomlabel = '';
                                $productUom =  $rfqProduct->getData('umo');
                                if ($productUom>0 && array_key_exists($productUom, $uomOptions)) {
                                    $uomlabel = $uomOptions[$productUom];
                                }
                                $item_info[$rfqProduct->getRfqProductId()]['prod_id'] = $rfqProduct->getRfqProductId();
                                $item_info[$rfqProduct->getRfqProductId()]['name'] = $rfqProduct->getName();
                                $item_info[$rfqProduct->getRfqProductId()]['qty'] = $rfqProduct->getQty() . ' ' . $uomlabel;
                                $item_info[$rfqProduct->getRfqProductId()]['item_identifier'] = $rfqProduct->getItemIdentifier();
                                $item_info[$rfqProduct->getRfqProductId()]['price'] = $this->_pricingHelper->currency($rfqProduct->getTargetPrice(), true, false);
                                /*$category_ids = $rfqProduct->getCategoryIds();
                                $category_ids =  explode(",",$category_ids);
                                if(is_array($category_ids )){
                                foreach($category_ids as $category_id)
                                $parentIds = $this->getParentCategory($category_id);
                                $parentIds[]= $category_id;
                                $rfqCategoryIds  = $parentIds;
                                }*/
                            }
                        }
                        $rfqCategoryIds  = array_unique($rfqCategoryIds);
                        $cedvendorcollection = $this->_vendorFactory->create()->getCollection()
                        ->addAttributeToFilter('industry', ['in'=> $rfqCategoryIds])
                        ->addAttributeToSelect('*')
                        ->addAttributeToSelect('customer_id')
                        ->addAttributeToSelect('email')
                        ->addAttributeToSelect('contact_number')
                        ->addAttributeToSelect('group')
                        ->addAttributeToSelect('status')
                        ->addAttributeToSelect('rfqseller_isvisible');
                        if ($cedvendorcollection->getSize()) {
                            foreach ($cedvendorcollection as $vendor) {
                                $vendorEntityid =  $vendor->getData('entity_id');
                                $subject = "New Non-Catalog Quote Request #" . $rfq->getQuoteIncrementId();
                                $vendorEmail =  $vendor->getData('email');
                                $vendorName = $vendor->getData('name');
                                $template_variables = [
                                   'dynamic_subject' => $subject,
                                    'quote_id' => '#' . $rfq->getQuoteIncrementId(),
                                    'quote_status' => $label,
                                    'vendor_name'=> $vendorName,
                                    'items' => $item_info,
                                    'totals' => $totals,
                                    'customer_name' => $this->helper->obfuscate($customerName, 3),
                                    'company_name'=> $this->helper->obfuscate($company_name, 3),
                                    'rfqdetailurl'=> $rfqdetailUrl
                                    ];
                                $isMailSend = $this->sendMarketplaceVendorEmail($template_variables, $vendorEmail);
                                if ($isMailSend) {
                                    $this->_logger->warning(" New Public RFQ Email Send To " . $vendorEmail, $template_variables);
                                    $smsto = $this->smsnotificationHelper->getCountryNumber($vendorEntityid);
                                    //$msg =  "Hello ".$vendorName."  you have recived Non-Catalog Quote Request #".$rfq->getQuoteIncrementId();
                                    $smsVariables = ['vendorname'=>$vendorName,'quote_id'=>$rfq->getQuoteIncrementId(),'url'=>$siteUrl];
                                    $smsContent = (string) $this->helper->getSMSTemplate($this->helper::SMS_CONFIG_VENDOR_NONCATALOG_RFQ, $smsVariables);
                                    $this->sendSMSNotification($smsto, $smsContent);
                                }
                            }
                        }
                        $rfq->setData('is_publicmail_send', 1)->save();
                    }
                }
            }
        } catch (\Exception $e) {
            //throw new \Exception( $e->getMessage() );
            //echo  $e->getMessage() ;
            $this->_logger->warning(" New Public RFQ Email Send Error " . $e->getMessage());
        }
    }

    private function sendSMSNotification($mobile, $msg)
    {
        if (isset($mobile) && isset($msg) && $mobile!='' && $msg!='') {
            return $this->smsnotificationHelper->sendSms($mobile, $msg);
        }
    }

    private function sendMarketplaceVendorEmail($template_variables, $vendorEmail)
    {
        $template = 'quote_submit_vendor_email_template';
        return  $this->helper->sendEmail($template, $template_variables, $vendorEmail);
    }
}
