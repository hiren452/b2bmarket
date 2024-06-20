<?php

namespace Matrix\NoncatalogueRfq\Cron;

//@codingStandardsIgnoreStart
class NonmktVendorRfqMail
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /** @var Noncatalogquote|NoncatalogRfqFactory
     */
    protected $_noncatalogquote;

    /**
     * @var RfqnonMktVendorFactory|RfqNonMarketVendorFactory
     */
    protected $_rfqnonMktVendorFactory;

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
        \Matrix\NoncatalogueRfq\Model\RfqNonMarketVendorFactory $rfqnonMktVendorFactory,
        \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Matrix\NoncatalogueRfq\Helper\Data $helper,
        \Ced\CsTwiliosmsnotification\Helper\Data $smsnotificationHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->urlInterface = $urlInterface;
        $this->_noncatalogquote = $noncatalogquote;
        $this->_noncatalogqProduct = $noncatalogqProduct;
        $this->_rfqnonMktVendorFactory = $rfqnonMktVendorFactory;
        $this->quoteStatus = $quoteStatus;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        $this->smsnotificationHelper =  $smsnotificationHelper;
        $this->urlInterface = $urlInterface;
        $this->_pricingHelper = $pricingHelper;
        $this->_logger = $logger;
    }

    public function execute()
    {

        try {
            $cur_time = date('Y-m-d');
            $collection = $this->_noncatalogquote->create()
            ->getCollection()
            //->addFieldToFilter('rfq_type', $this->helper::NONCATALOG_RFQ_TYPE_PUBLIC)
            ->addFieldToFilter('status', ['neq'=>\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED]);
            //->addFieldToFilter('respons_date', array('lt' => date("Y-m-d")));
            /*echo $collection->getSelect()."<br />";
            echo "Count RFQ =".$collection->getSize()."<br />";*/
            if ($collection->getSize()) {
                foreach ($collection as $rfq) {

                    $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/', ['id'=>$rfq->getData('rfq_id')]);
                    $rfqdetailUrl = $this->urlInterface->getUrl('csmarketplace/account/login', ['referer' => base64_encode($rfqUrl)]);
                    $item_info =  [];
                    $totals =  [];

                    $customerName = $rfq->getData('name');
                    $company_name = $rfq->getData('company_name');
                    $label = $this->quoteStatus->getOptionText($rfq->getStatus());
                    $totals['subtotal'] = $this->_pricingHelper->currency($rfq->getRfqTotalPrice(), true, false);
                    $totals['grandtotal'] = $this->_pricingHelper->currency($rfq->getRfqTotalPrice(), true, false);
                    $nonCatalogQuoteProductCollection = $this->_noncatalogqProduct->create()
                    ->getCollection()
                    ->addFieldToFilter('rfq_id', $rfq->getData('rfq_id'));
                    //echo "<br />".$nonCatalogQuoteProductCollection->getSelect()."<br />";
                    //echo "<br/>Product  COunt=".$nonCatalogQuoteProductCollection->getSize()."<br />";
                    if ($nonCatalogQuoteProductCollection->getSize()) {
                        $uomOptions = $this->helper->getUomOptions();
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
                    $nonMktVendorCollection = $this->_rfqnonMktVendorFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('rfq_id', $rfq->getData('rfq_id'))
                    ->addFieldToFilter('is_emailsend', ['eq'=>0]);
                    echo "<br/>Non MKT Bendor  Count=" . $nonMktVendorCollection->getSize() . "<br />";
                    if ($nonMktVendorCollection->getSize()) {
                        foreach ($nonMktVendorCollection as $nonMktvendor) {

                            $nonCatVendorEmail =  $nonMktvendor->getData('email');
                            $vendor_registration_link = $this->urlInterface->getUrl('csmarketplace/account/register/');
                            $template_variables = [
                                'quote_id' => '#' . $rfq->getQuoteIncrementId(),
                                'quote_status' => $label,
                                'vendor_companyname'=> $nonMktvendor->getData('company_name'),
                                'vendor_email'=> $nonMktvendor->getData('email'),
                                'vendor_phone'=> $nonMktvendor->getData('phone'),
                                'vendor_address'=> $nonMktvendor->getData('address'),
                                'items' => $item_info,
                                'totals' => $totals,
                                'name' => $this->helper->obfuscate($customerName),
                                'company_name'=> $company_name,
                                'link' => $vendor_registration_link,
                                'rfqdetailurl'=> $rfqdetailUrl
                                ];
                            echo "send Emaul TO=" . $nonCatVendorEmail . "<br />";
                            echo "<br>Template Var</br>";
                            echo "<hr />";

                            $isMailSend = $this->getNonMarketplaceVendorEmail($template_variables, $nonCatVendorEmail);
                            //$isMailSend = false;
                            if ($isMailSend) {
                                echo "<br>Mail Send Succeddfully,</br>";
                                $nonMktvendor->setData('is_emailsend', 1)->save();
                            }
                        }
                    }
                }
            }

        } catch (\Exception $e) {

            //throw new \Exception( $e->getMessage() );
            echo  $e->getMessage();
        }
    }

    private function getNonMarketplaceVendorEmail($template_variables, $nonMktVendorEmail)
    {
        $template = 'quote_submit_nonmktvendor_email_template';
        return  $this->helper->sendEmail($template, $template_variables, $nonMktVendorEmail);
    }
}
//@codingStandardsIgnoreEnd
