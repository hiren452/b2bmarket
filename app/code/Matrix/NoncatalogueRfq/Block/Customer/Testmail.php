<?php
//@codingStandardsIgnoreStart
namespace Matrix\NoncatalogueRfq\Controller\Index;

use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Matrix\NoncatalogueRfq\Helper\Data;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\RfqNonMarketVendorFactory;
use Matrix\NoncatalogueRfq\Model\RfqProductFactory;
use Matrix\NoncatalogueRfq\Model\RfqVendorFactory;

class Testmail extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Noncatalogquote|NoncatalogRfqFactory
     */
    protected $_noncatalogquote;

    /**
     * @var NoncatalogqProduct|RfqProductFactory
     */
    protected $_noncatalogqProduct;

    /**
     * @var RfqVenforFactory|RfqVendorFactory
     */
    protected $_rfqVendorFactory;

    /**
     * @var RfqnonMktVendorFactory|RfqNonMarketVendorFactory
     */
    protected $_rfqnonMktVendorFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var Data
     */
    protected $helper;

    protected $_rfqTemplateFactory;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    protected $customerModelFactory;

    protected $_pricingHelper;

    protected $smsnotificationHelper;

    protected $_logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        Session $customerSession,
        CustomerFactory $customerModelFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        NoncatalogRfqFactory $noncatalogquote,
        RfqProductFactory $noncatalogqProduct,
        RfqVendorFactory $rfqVendorFactory,
        RfqNonMarketVendorFactory $rfqnonMktVendorFactory,
        ManagerInterface $messageManager,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqTemplateFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        QuoteStatus $quoteStatus,
        \Matrix\NoncatalogueRfq\Logger\Logger $logger,
        \Ced\CsTwiliosmsnotification\Helper\Data $smsnotificationHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        Data $helper
    ) {

        $this->resultPageFactory = $pageFactory;
        $this->session = $customerSession;
        $this->customerModelFactory = $customerModelFactory;
        $this->messageManager       = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->_noncatalogquote = $noncatalogquote;
        $this->_noncatalogqProduct = $noncatalogqProduct;
        $this->_rfqVendorFactory = $rfqVendorFactory;
        $this->_rfqnonMktVendorFactory = $rfqnonMktVendorFactory;
        $this->_rfqTemplateFactory = $rfqTemplateFactory;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        $this->_pricingHelper = $pricingHelper;
        $this->quoteStatus = $quoteStatus;
        $this->smsnotificationHelper =  $smsnotificationHelper;
        $this->_logger = $logger;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $nonCatalogQuoteModel = $this->_noncatalogquote->create()->load(5);
        $this->_eventManager->dispatch(
            'noncatalogrequesttoquote_submit_success',
            ['noncatalogrequesttoquote_controller' => $this, 'noncatalogRfq' => $nonCatalogQuoteModel]
        );

        //$this->sendvendorSms();
        //echo $this->helper->getEmailTemplateId('quote_submit_vendor_email_template');
        //$this->quote_submit_vendor_email_template(); // Customer  or vendor mail
        //$this->new_nonmarketplacevendor_email(); // Customer  or vendor mail
        //$this->noncatalog_rfqsubmit_success_email_template(); // Customer  or vendor mail
        /*if(!$this->helper->isEnable()){
            $this->_redirect ( 'customer/account/login' );
            return;
        }*/

        /*$this->_logger->warning("call Class Methd <--". __METHOD__ .'--> \n approx go to  <-- line no '. __LINE__ .'-->');
        $this->_logger->warning(" TEST MAIL Hello This is our Custom log file For Non-Catalog RFQ");
        */

        try {

            $customerName = "Pritam Biswas";
            $company_name = "ABC Ltd";
            $email = "scroot.tiger@gmail.com";
            $arrrfqtype = $this->helper->getRFQuoteTypes();
            $rfqtype =  $arrrfqtype[1];
            $item_info = [];
            $item_info[1]['prod_id'] = 25;
            $item_info[1]['name'] = 'Mobile Cover';
            $item_info[1]['qty'] = 500;
            $item_info[1]['item_identifier'] = 'UT-5258';
            $item_info[1]['price'] = 120.00;

            $totals['subtotal'] = 2300;
            $totals['grandtotal'] = 2300;

            $template_variables = [
                'quote_id' => '#NONCAT2222222',
                'quote_status' => 'Pending',
                'items' => $item_info,
                'totals' => $totals,
                'name' => $customerName,
                'company_name'=> $company_name,
                'rfqtype' => $rfqtype
            ];

            $template = 'noncatalogrfq_submit_email_template';

            $ismailSucces =$this->helper->sendAdminEmail($template, $template_variables, $email);
            if ($ismailSucces) {
                echo "<br/>Mail Send Sussessfully";
            } else {
                echo "<br/>Mail Not Send Sussessfully";
            }

            /*echo "Send Email to Admin<br />";
            echo "<br />Template=". $template;
            echo "<br />customerEmail=".$email;
            */
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function quote_submit_vendor_email_template()
    {

        $customerName = "Pritam Biswas";
        $company_name = "ABC Global Ltd";
        $vendorEmail = "pritambiswas@matrixnmedia.com";
        $arrrfqtype = $this->helper->getRFQuoteTypes();
        $uomOptions =  $this->helper->getUomOptions();
        $rfqtype =  $arrrfqtype[1];
        $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/', ['id'=>10]);
        $rfqdetailUrl = $this->urlInterface->getUrl('csmarketplace/account/login', ['referer' => base64_encode($rfqUrl)]);
        $item_info = [];
        $item_info[1]['prod_id'] = 25;
        $item_info[1]['name'] = 'Mobile Cover';
        $item_info[1]['qty'] = 500;
        $item_info[1]['item_identifier'] = 'UT-5258';

        $item_info[1]['price'] = $this->_pricingHelper->currency(120.00, true, false);
        $totals['subtotal'] = $this->_pricingHelper->currency(2300, true, false);
        $totals['grandtotal'] = $this->_pricingHelper->currency(2300, true, false);
        $template_variables = [
                          'dynamic_subject' => 'TES Non Catalof RFQ Mail',
                           'quote_id' => '#NONCAT2222222',
                           'quote_status' => 'Pending',
                           'vendor_name'=> 'TEST TEST',
                           'items' => $item_info,
                           'totals' => $totals,
                           'customer_name' => $this->helper->obfuscate($customerName, 3),
                           'company_name'=> $this->helper->obfuscate($company_name, 3),
                           'rfqdetailurl'=> $rfqdetailUrl,
                           ];

        $template = 'quote_submit_vendor_email_template';

        $isMailSend = $this->helper->sendEmail($template, $template_variables, $vendorEmail);
        if ($isMailSend) {
            echo "<br/>Mail Send Sussessfully";
        } else {
            echo "<br/>Mail Not Send Sussessfully";
        }
    }

    public function new_nonmarketplacevendor_email()
    {
        $customerName = "Pritam Biswas";
        $company_name = "ABC Global Ltd";
        $vendorEmail = "pritambiswas@matrixnmedia.com";
        $arrrfqtype = $this->helper->getRFQuoteTypes();
        $uomOptions =  $this->helper->getUomOptions();
        $rfqtype =  $arrrfqtype[1];
        $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/', ['id'=>10]);
        $rfqdetailUrl = $this->urlInterface->getUrl('csmarketplace/account/login', ['referer' => base64_encode($rfqUrl)]);
        $item_info = [];
        $item_info[1]['prod_id'] = 25;
        $item_info[1]['name'] = 'Mobile Cover';
        $item_info[1]['qty'] = 500;
        $item_info[1]['item_identifier'] = 'UT-5258';

        $item_info[1]['price'] = $this->_pricingHelper->currency(120.00, true, false);
        $totals['subtotal'] = $this->_pricingHelper->currency(2300, true, false);
        $totals['grandtotal'] = $this->_pricingHelper->currency(2300, true, false);
        $vendor_registration_link = 'www.b2bmarket.com/customer/account/create';
        $template_variables = [
                           'quote_id' =>  '#NONCAT2222222',
                           'quote_status' => 'Pending',
                           'vendor_companyname'=> 'Demo Company',
                           'vendor_email'=> 'democomapny@gmail.com',
                           'vendor_phone'=> '985485969',
                           'vendor_address'=> 'Fake Street, Fake State',
                           'items' => $item_info,
                           'totals' => $totals,
                           'name' => $this->helper->obfuscate($customerName),
                           'company_name'=> $company_name,
                           'link' => $vendor_registration_link,
                           'rfqdetailurl'=> $rfqdetailUrl
                           ];
        $template = 'quote_submit_nonmktvendor_email_template';
        $isMailSend = $this->helper->sendEmail($template, $template_variables, $vendorEmail);
        if ($isMailSend) {
            echo "<br/>Mail Send Sussessfully";
        } else {
            echo "<br/>Mail Not Send Sussessfully";
        }
    }

    public function noncatalog_rfqsubmit_success_email_template()
    {
        $customerName = "Pritam Biswas";
        $company_name = "ABC Ltd";
        $email = "pritambiswas@matrixnmedia.com";
        $arrrfqtype = $this->helper->getRFQuoteTypes();
        $rfqtype =  $arrrfqtype[1];
        $item_info = [];
        $item_info[1]['prod_id'] = 25;
        $item_info[1]['name'] = 'Mobile Cover';
        $item_info[1]['qty'] = 500;
        $item_info[1]['item_identifier'] = 'UT-5258';
        $item_info[1]['price'] = 120.00;

        $totals['subtotal'] = 2300;
        $totals['grandtotal'] = 2300;

        $template_variables = [
            'name' => $customerName,
            'quote_id' => '#NONCAT2222222',
               'quote_status' => 'Pending',
             ];
        $template = 'noncatalog_rfqsubmit_success_email_template';
        $ismailSucces =$this->helper->sendEmail($template, $template_variables, $email);
        if ($ismailSucces) {
            echo "<br/>Mail Send Sussessfully";
        } else {
            echo "<br/>Mail Not Send Sussessfully";
        }
    }

    private function isApproveCustomer()
    {
        $customerId = $this->session->getCustomer()->getId();
        if (!isset($customerId) || $customerId<=0) {
            return 0;
        }
        $customer = $this->customerModelFactory->create()->load($customerId);
        $isApproveCustomer =  0;
        if ($customer->getIsApprove()) {
            $isApproveCustomer = 1;
        }
        return $isApproveCustomer;
    }

    private function createNonMarketPlacevendos($data, $rfq_id)
    {

        $rfqnonMarketVendor = $this->_rfqnonMktVendorFactory->create();
        //$rfq_id  = 10;
        $vendor_id = null;
        $company_name = $data['companyname'];
        $phone =  $data['phone'];
        $email = $data['email'];
        $url =  $data['url'];
        $address =  $data['address'];
        $is_emailsend = 0;

        $rfqnonMarketVendor->setData('rfq_id', $rfq_id);
        $rfqnonMarketVendor->setData('vendor_id', $vendor_id);
        $rfqnonMarketVendor->setData('company_name', $company_name);
        $rfqnonMarketVendor->setData('email', $email);
        $rfqnonMarketVendor->setData('phone', $phone);
        $rfqnonMarketVendor->setData('url', $url);
        $rfqnonMarketVendor->setData('address', $address);
        $rfqnonMarketVendor->setData('is_emailsend', $is_emailsend);
        return $rfqnonMarketVendor->save();
    }

    public function sendvendorSms()
    {
        //$url =  $rfqUrl = $this->urlInterface->getUrl('vendornoncatrfq/rfq/view/',array('id'=>10));
        $url =  $rfqUrl = $this->urlInterface->getUrl();
        //$smsVariables = array('vendorname'=>'Goutam','quote_id'=>'NCQ000125','url'=>$url);
        //$smsContent =   $this->helper->getSMSTemplate($this->helper::SMS_CONFIG_VENDOR_PUBLIC_NONCATALOG_RFQ,$smsVariables);
        $smsVariables = ['rfq_type'=>'Private','quote_id'=>'NCQ000125','customer_email'=>'abcdtest@gmail.com'];
        $smsContent =   $this->helper->getSMSTemplate($this->helper::SMS_CONFIG_ADMIN_NONCATALOG_RFQ, $smsVariables);
        //$smsto = $this->smsnotificationHelper->getCountryNumber($vendorEntityid);
        //$msg =  "Hello ".$vendorName."  you have recived Non-Catalog Quote Request #".$rfq->getQuoteIncrementId();
        //$isSucess = $this->smsnotificationHelper->sendSms('+918240378981',$smsContent);
        //$twilioHelper->sendSms('+918240378981', 'Hello Goutam');
        $adminMobile =  $this->helper->getConfigValue('noncatalogrfq_configuration/smsnotification/admin_noncatalogrfq_mobile', 1);
        $isSucess = $this->smsnotificationHelper->sendSms('+918240378981', $smsContent);
        if ($isSucess) {
            echo '<br /> SMS Success ';
        } else {
            echo '<br /> SMS Failed ';
        }

        echo  "<br />" . $smsContent;
    }

    private function getCustomerId()
    {
        return $this->session->getCustomer()->getId();
    }
}
//@codingStandardsIgnoreEnd
