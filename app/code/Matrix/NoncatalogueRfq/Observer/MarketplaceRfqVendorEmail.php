<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Framework\Event\ObserverInterface;

class MarketplaceRfqVendorEmail implements ObserverInterface
{
    protected $vendorFactory;
    protected $urlInterface;
    protected $session;
    protected $rfqVendorFactory;
    protected $quoteStatus;
    protected $helper;
    protected $logger;

    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory          $vendorFactory,
        \Magento\Framework\UrlInterface                 $urlInterface,
        \Magento\Customer\Model\Session                 $customerSession,
        \Matrix\NoncatalogueRfq\Helper\Data             $helper,
        \Matrix\NoncatalogueRfq\Model\RfqVendorFactory  $rfqVendorFactory,
        \Ced\RequestToQuote\Model\Source\QuoteStatus    $quoteStatus,
        \Magento\Framework\Pricing\Helper\Data          $pricingHelper,
        \Psr\Log\LoggerInterface                        $logger,
        \Ced\CsTwiliosmsnotification\Helper\Data        $smsnotificationHelper,
        \Matrix\NoncatalogueRfq\Model\RfqProductFactory $noncatalogqProduct
    ) {
        $this->rfqVendorFactory = $rfqVendorFactory;
        $this->vendorFactory = $vendorFactory;
        $this->urlInterface = $urlInterface;
        $this->session = $customerSession;
        $this->quoteStatus = $quoteStatus;
        $this->_pricingHelper = $pricingHelper;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->smsnotificationHelper = $smsnotificationHelper;
        $this->_noncatalogqProduct = $noncatalogqProduct;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $rfqData = $observer->getData('noncatalogRfq');
        $vendorData = $observer->getData('noncatalogrequesttoquote_controller');
        $rfqId = $rfqData->getRfqId();
        $uomOptions = $this->helper->getUomOptions();

        $rfqVendorCollection = $this->rfqVendorFactory->create()
            ->getCollection()
            ->addFieldToFilter('rfq_id', $rfqId)
            ->addFieldToFilter('is_emailsend', ['eq' => 0]);
        try {
            if ($rfqVendorCollection->getSize()) {
                $item_info =  [];
                $totals =  [];
                foreach ($rfqVendorCollection as $rfqVendor) {
                    $subject = "New Non-Catalog Quote Request #" . $rfqData->getQuoteIncrementId();
                    $vendor = $this->vendorFactory->create()->load($rfqVendor->getData('vendor_id'));
                    $vendorEntityid = $vendor->getData('entity_id');
                    $vendorEmail = $vendor->getData('email');
                    $vendorName = $vendor->getData('name');
                    $totals['subtotal'] = $this->_pricingHelper->currency($rfqData->getRfqTotalPrice(), true, false);
                    $totals['grandtotal'] = $this->_pricingHelper->currency($rfqData->getRfqTotalPrice(), true, false);
                    $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/', ['id' => $rfqData->getData('rfq_id')]);
                    $rfqdetailUrl = $this->urlInterface->getUrl('csmarketplace/account/login', ['referer' => base64_encode($rfqUrl)]);
                    $customerName = $rfqData->getData('name');
                    $company_name = $rfqData->getData('company_name');
                    $nonCatalogQuoteProductCollection = $this->_noncatalogqProduct->create()
                        ->getCollection()
                        ->addFieldToFilter('rfq_id', $rfqData->getData('rfq_id'));
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

                        }
                    }
                    $template_variables = [
                        'dynamic_subject' => $subject,
                        'quote_id' => '#' . $rfqData->getQuoteIncrementId(),
                        'quote_status' => $this->quoteStatus->getOptionText($rfqData->getStatus()),
                        'vendor_name' => $vendor->getData('name'),
                        'items' => $item_info,
                        'totals' => $totals,
                        'customer_name' => $this->helper->obfuscate($customerName),
                        'company_name' => $this->helper->obfuscate($company_name),
                        'rfqdetailurl' => $rfqdetailUrl,
                    ];
                    $isMailSend = $this->sendMarketplaceVendorEmail($template_variables, $vendorEmail);
                    if ($isMailSend) {
                        $smsto = $this->smsnotificationHelper->getCountryNumber($vendorEntityid);
                        $vendorName = $this->checkVendorNamelength($vendor->getData('name'));
                        $smsVariables = ['vendorname' => $vendorName, 'quote_id' => $rfqData->getQuoteIncrementId(), 'url' => $this->urlInterface->getUrl()];
                        $smsContent = (string)$this->helper->getSMSTemplate($this->helper::SMS_CONFIG_VENDOR_NONCATALOG_RFQ, $smsVariables);
                        if (isset($smsto) && isset($smsContent) && $smsto != '' && $smsContent != '') {
                            try {
                                $this->smsnotificationHelper->sendSms($smsto, $smsContent);
                            } catch (\Exception $e) {
                                $this->logger->critical($e->getMessage());
                            }
                        }
                        $rfqVendor->setData('is_emailsend', 1)->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function sendMarketplaceVendorEmail($template_variables, $nonMktVendorEmail)
    {
        $template = 'quote_submit_vendor_email_template';
        return $this->helper->sendEmail($template, $template_variables, $nonMktVendorEmail);
    }

    private function checkVendorNamelength($vendorName)
    {
        if (isset($vendorName) && strlen($vendorName) > 20) {
            $vendorName = substr($vendorName, 0, 16) . '...';
        }
        return $vendorName;
    }
}
