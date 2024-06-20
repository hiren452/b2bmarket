<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Matrix\NoncatalogueRfq\Helper\Data  as Helper;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

/**
 * Class Success
 */
class NonMarketplaceRfqInvite implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    protected $helper;

    protected $noncatalogRfqFactory;

    protected $storeManager;

    protected $smsnotificationHelper;

    protected $_pricingHelper;

    protected $_noncatalogqProduct;

    protected $_rfqnonMktVendorFactory;

    protected $urlInterface;

    protected $quoteStatus;

    protected $_logger;

    /**
     * Success constructor.
     * @param Session $customerSession
     * @param Helper $helper
     * @param NoncatalogRfqFactory $noncatalogRfqFactory
     * @param CustomerCart $cart
     */
    public function __construct(
        Session $customerSession,
        NoncatalogRfqFactory $noncatalogRfqFactory,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Ced\CsTwiliosmsnotification\Helper\Data $smsnotificationHelper,
        \Matrix\NoncatalogueRfq\Model\RfqProductFactory $noncatalogqProduct,
        \Matrix\NoncatalogueRfq\Model\RfqNonMarketVendorFactory $rfqnonMktVendorFactory,
        \Ced\RequestToQuote\Model\Source\QuoteStatus $quoteStatus,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Matrix\NoncatalogueRfq\Logger\Logger $logger,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->noncatalogRfqFactory = $noncatalogRfqFactory;
        $this->smsnotificationHelper =  $smsnotificationHelper;
        $this->storeManager = $storeManager;
        $this->helper = $helper;

        $this->urlInterface = $urlInterface;
        $this->_pricingHelper = $pricingHelper;
        //$this->_noncatalogquote = $noncatalogquote;
        $this->_noncatalogqProduct = $noncatalogqProduct;
        $this->_rfqnonMktVendorFactory = $rfqnonMktVendorFactory;
        $this->quoteStatus = $quoteStatus;
        $this->dateTime = $dateTime;

        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_logger->info('NonMarketplaceRfqInvite observer called');
        try {
            $noncatalogRfq = $observer['noncatalogRfq'];
            $rfqId = $noncatalogRfq->getData('rfq_id');
            $this->_logger->info('called NonMarketplaceRfqInvite RFQ ID' . $rfq_id);
            if (!isset($rfqId) || $rfqId<=0) {
                return;
            }
            $arrrfqtype = $this->helper->getRFQuoteTypes();
            $rfqModel = $this->noncatalogRfqFactory->create()->load($rfqId);
            $this->_logger->info('called NonMarketplaceRfqInvite RFQ DATA', $rfqModel->getData());
            $cur_time = date('Y-m-d');
            $rfq = $this->noncatalogRfqFactory->create()
            ->getCollection()
            ->addFieldToFilter('rfq_id', $rfqId)
            ->addFieldToFilter('rfq_type', $this->helper::NONCATALOG_RFQ_TYPE_PUBLIC)
            //->addFieldToFilter('status',array('neq'=>\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED))
            ->getFirstItem();
            if ($rfq && $rfq->getRfqId()>0) {
                $this->_logger->info('NonMarketplaceRfqInvite PUBLIC RFQ DATA', $rfq->getData());
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
                $this->_logger->info("Product  COunt=" . $nonCatalogQuoteProductCollection->getSize());
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
                $this->_logger->info("Non MKT Bendor  Count=" . $nonMktVendorCollection->getSize());

                if ($nonMktVendorCollection->getSize()) {
                    foreach ($nonMktVendorCollection as $nonMktvendor) {
                        /*echo "<h2>Non Marketplace Vendor</h2>";*/

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
                        /*echo "send Email TO=".  $nonCatVendorEmail."<br />";
                        echo "<br>Template Var</br>";
                        echo "<hr />";*/

                        $this->_logger->info("send Email TO=" . $nonCatVendorEmail);
                        $this->_logger->info(" Email Template Var", $template_variables);
                        $isMailSend = $this->getNonMarketplaceVendorEmail($template_variables, $nonCatVendorEmail);
                        ///$isMailSend = false;
                        if ($isMailSend) {
                            //echo "<br>Mail Send Succeddfully,</br>";
                            $nonMktvendor->setData('is_emailsend', 1)->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->info("Error " . $e->getMessage());

        }
    }

    private function getNonMarketplaceVendorEmail($template_variables, $nonMktVendorEmail)
    {
        $template = 'quote_submit_nonmktvendor_email_template';
        return  $this->helper->sendEmail($template, $template_variables, $nonMktVendorEmail);
    }
}
