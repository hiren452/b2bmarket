<?php

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

class Submit extends \Magento\Framework\App\Action\Action
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

    protected $_categoryFactory;

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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        QuoteStatus $quoteStatus,
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
        $this->quoteStatus = $quoteStatus;
        $this->_categoryFactory = $categoryFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        /*==== START category  */
        /*$categoryId = 5;
        $leveltwocategoryid =$this->getlevelTwoCategoryId($categoryId);
        echo "<br/>Primay Category Id=".$categoryId;
        echo "<br/> Level Category Id=".$leveltwocategoryid;*/
        /*END Catgory*/

        if (!$this->helper->isEnable()) {
            $this->_redirect('customer/account/login');
            return;
        }

        if (! $this->session->isLoggedIn()) {
            //$redirectUrl = $this->_redirect->getRefererUrl('noncatalogrequesttoquote/index/index/');
            $redirectUrl = $this->urlInterface->getUrl('noncatalogrequesttoquote/index/index/');
            $this->messageManager->addErrorMessage(__('Please login first before creating RFQ Template.'));
            $login_url = $this->urlInterface
            ->getUrl('customer/account/login', ['referer' => base64_encode($redirectUrl)]);
            // Redirect to login URL
            // @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($login_url);
            return $resultRedirect;
        }
        $customerId  =  $this->getCustomerId();
        $customerEmal  =  $this->session->getCustomer()->getEmail();
        $postData = $this->getRequest()->getParams();
        $customerData = $this->session->getCustomer()->getData();
        //Reset RFQ POP UP Form Data
        $this->session->unsNoncatrfqpopuppostData();
        //$this->noncatrfqsession->getData('nonCatrRqPopupPostData')

        /* START RFQ Templates */
        $saveastemplate = (isset($postData['saveastemplate'])) ? $postData['saveastemplate'] : 0;
        if ($saveastemplate==1) {

            $templateid = isset($postData['rfq_templateid']) ? $postData['rfq_templateid'] : 0;
            $rfqtemplate_model = $this->_rfqTemplateFactory->create();
            if (isset($templateid) && $templateid  >0) {
                $rfqtemplate_model = $rfqtemplate_model->load($templateid);
            }
            $template_name = $postData['template_name'];
            $form_data = json_encode($postData);// $postparams['form_data'];
            $is_emailsend  = 0;
            $status = 2;
            if ($rfqtemplate_model->getId()) {
                //$unsavedRfqModel->setData('customer_id', $customerId);
                $rfqtemplate_model->setData('template_name', $template_name);
                $rfqtemplate_model->setData('form_data', $form_data);
                $rfqtemplate_model->setData('is_emailsend', $is_emailsend);
                $rfqtemplate_model->setData('updated_at', $this->dateTime->gmtDate());
                $rfqtemplate_model->setData('status', $status);
            } else {
                $rfqtemplate_model->setData('customer_id', $customerId);
                $rfqtemplate_model->setData('template_name', $template_name);
                $rfqtemplate_model->setData('form_data', $form_data);
                $rfqtemplate_model->setData('is_emailsend', $is_emailsend);
                $rfqtemplate_model->setData('updated_at', $this->dateTime->gmtDate());
                $rfqtemplate_model->setData('status', $status);
            }
            $rfqtemplate_model->save();
        }
        /* END RFQ Templates */

        /*Start Server side valiation*/
        $marketplace_suppler_ids = isset($postData['marketplace_suppler_ids']) ? $postData['marketplace_suppler_ids'] : null;
        //$suppliertype = (isset($postData['suppliertype']))? $postData['suppliertype'] : 0;
        $suppliertype = (isset($postData['suppliertype'])) ? $postData['suppliertype'] : 0;
        if ($suppliertype== $this->helper::NONCATALOG_RFQ_TYPE_PRIVATE && !is_array($marketplace_suppler_ids)) {
            $this->messageManager->addErrorMessage(__('Please select Market Place Supplier.'));
            return $this->_redirect('noncatalogrequesttoquote/index/index');
        }
        $splr_invite = isset($postData['splr_invite']) ? $postData['splr_invite'] : null;
        $nonmarket_supplist_data = $postData['nonmarket_supplist_data'];
        $nonmarketsupplistArray = json_decode('[' . $nonmarket_supplist_data . ']', true);
        if ($splr_invite==1 && !is_array($nonmarketsupplistArray)) {
            $this->messageManager->addErrorMessage(__('Please Enter Data Non-market Place Supplier.'));
            return $this->_redirect('noncatalogrequesttoquote/index/index');
        }
        /*End Server side valiation*/
        try {
            $item_info = [];
            $totals = [];
            $noncatalogquote_collection = $this->_noncatalogquote->create()->getCollection();
            if ($noncatalogquote_collection->getSize() > 0) {
                $qo_id =  $noncatalogquote_collection->getLastItem()->getRfqId();
                $qo_id = $qo_id + 1;
                $qoincId = 'NCQO' . sprintf("%05d", $qo_id);
            } else {
                $qoincId = 'NCQO00001';
            }
            //Create RFQ

            //level Two Category
            $leveltwocategoryid = 0;
            if (isset($postData['category_id']) && array_key_exists(0, $postData['category_id'])) {
                $categoryId = $postData['category_id'][0];
                $leveltwocategoryid =$this->getlevelTwoCategoryId($categoryId);
            }
            $nonCatalogQuoteModel = $this->_noncatalogquote->create();
            $templateName = $postData['name'];
            $company_name = $postData['companyname'];
            $vendor_type  = 1;
            $rfq_type = (isset($postData['suppliertype']) && $postData['suppliertype']>0) ? $postData['suppliertype'] : 1;
            $vendor_certifications =  isset($postData['splr_certifications']) ? $postData['splr_certifications'] : '';
            if (is_array($vendor_certifications)) {
                $vendor_certifications =  implode(',', $vendor_certifications);
            }
            $is_approve = ($this->isApproveCustomer()) ? 1 : 0;
            $vendor_oth_requirement = $postData['splr_otherreq'];
            $rfq_total_qty = $postData['productqty'];
            $rfq_total_price = $postData['targetprice'] * $postData['productqty'];
            $rfq_updated_qty = 0;
            $rfq_updated_price = 0;
            $shipment_method = $postData['shippingmethod'];
            $shipment_destination = $postData['destination'];
            $lead_time = $postData['lead_time'];
            $payment_terms = $postData['payment_terms'];
            $respons_date =  $postData['respons_date'];
            $status = 0;

            $nonCatalogQuoteModel->setData('customer_id', $customerId);
            $nonCatalogQuoteModel->setData('customer_email', $customerEmal);
            $nonCatalogQuoteModel->setData('is_approve', $is_approve);
            $nonCatalogQuoteModel->setData('quote_increment_id', $qoincId);
            $nonCatalogQuoteModel->setData('name', $templateName);
            $nonCatalogQuoteModel->setData('company_name', $company_name);
            $nonCatalogQuoteModel->setData('category_ids', $leveltwocategoryid); //Level Two Category For Vendor Industry Mapping
            $nonCatalogQuoteModel->setData('rfq_type', $rfq_type);
            $nonCatalogQuoteModel->setData('vendor_certifications', $vendor_certifications);
            $nonCatalogQuoteModel->setData('rfq_total_qty', $rfq_total_qty);
            $nonCatalogQuoteModel->setData('rfq_total_price', $rfq_total_price);
            $nonCatalogQuoteModel->setData('rfq_updated_qty', $rfq_updated_qty);
            $nonCatalogQuoteModel->setData('rfq_updated_price', $rfq_updated_price);
            $nonCatalogQuoteModel->setData('shipment_method', $shipment_method);
            $nonCatalogQuoteModel->setData('shipment_destination', $shipment_destination);
            $nonCatalogQuoteModel->setData('lead_time', $lead_time);
            $nonCatalogQuoteModel->setData('payment_terms', $payment_terms);
            $nonCatalogQuoteModel->setData('status', $status);
            $nonCatalogQuoteModel->setData('respons_date', $respons_date);

            $newRfq = $nonCatalogQuoteModel->save();
            $rfq_id = $newRfq->getData('rfq_id');

            //Create RFQ Prouct
            $nonCatalogQuoteProduct = $this->_noncatalogqProduct->create();
            $name = $postData['productname'];
            $desc = $postData['productdesc'];
            $item_identifier = $postData['productidentifier'];
            $qty = $postData['productqty'];
            $target_price = $postData['targetprice'];
            $tradeterms =  isset($postData['tradeterms']) ? $postData['tradeterms'] : '';
            if (is_array($tradeterms)) {
                $tradeterms =  implode(',', $tradeterms);
            }
            $category_ids = $postData['category_ids'];
            if (is_array($category_ids)) {
                $category_ids =  implode(",", $category_ids);
            }
            $umo = (isset($postData['umo'])) ? $postData['umo'] : '';
            $uom_others = (isset($postData['uom_others'])) ? $postData['uom_others'] : '';
            /*if($umo>0  && $umo == $this->helper::UOM_OPTION_OTHER_ID && $uom_others!=''){//UOM Others

            }*/
            $memo = (isset($postData['memo'])) ? $postData['memo'] : '';
            $uploads =  (isset($postData['uploads'])) ? $postData['uploads'] : null;
            $uploadsFileslist = '';
            $productUploads =  [];
            if (is_array($uploads)) {
                foreach ($uploads as $key => $fileName) {
                    list($fileNamepart, $fileExist) = explode(".", $fileName);
                    $productUploads[] = ["fileName"=>$fileName,"fileExt"=>$fileExist];
                }
                $uploadsFileslist    = json_encode($productUploads);
            }
            $sourcingpurpose =  isset($postData['productsourcingpurpose']) ? $postData['productsourcingpurpose'] : '';
            if (is_array($sourcingpurpose)) {
                $sourcingpurpose =  implode(',', $sourcingpurpose);
            }
            $payment_instruct = $postData['paymentterms'];
            $nonCatalogQuoteProduct->setData('rfq_id', $rfq_id);
            $nonCatalogQuoteProduct->setData('name', $name);
            $nonCatalogQuoteProduct->setData('desc', $desc);
            $nonCatalogQuoteProduct->setData('item_identifier', $item_identifier);
            $nonCatalogQuoteProduct->setData('qty', $qty);
            $nonCatalogQuoteProduct->setData('target_price', $target_price);
            $nonCatalogQuoteProduct->setData('category_ids', $category_ids);
            $nonCatalogQuoteProduct->setData('uploads', $uploadsFileslist);
            $nonCatalogQuoteProduct->setData('tradeterms', $tradeterms);
            $nonCatalogQuoteProduct->setData('umo', $umo);
            $nonCatalogQuoteProduct->setData('umo_other', $uom_others);
            $nonCatalogQuoteProduct->setData('memo', $memo);
            $nonCatalogQuoteProduct->setData('sourcingpurpose', $sourcingpurpose);
            $nonCatalogQuoteProduct->setData('payment_instruct', $payment_instruct);
            $nonCatalogQuoteProduct->save();

            $item_info[$nonCatalogQuoteProduct->getRfqProductId()]['prod_id'] = $nonCatalogQuoteProduct->getRfqProductId();
            $item_info[$nonCatalogQuoteProduct->getRfqProductId()]['name'] = $nonCatalogQuoteProduct->getName();
            $item_info[$nonCatalogQuoteProduct->getRfqProductId()]['qty'] = $nonCatalogQuoteProduct->getQty();
            $item_info[$nonCatalogQuoteProduct->getRfqProductId()]['item_identifier'] = $nonCatalogQuoteProduct->getItemIdentifier();
            $item_info[$nonCatalogQuoteProduct->getRfqProductId()]['price'] = $nonCatalogQuoteProduct->getTargetPrice();

            //Create RFQ Market Place Vendor
            if ($this->helper->getConfigValue('noncatalogrfq_configuration/active/allow_private_noncatrfq')) {
                $marketplace_suppler_ids = isset($postData['marketplace_suppler_ids']) ? $postData['marketplace_suppler_ids'] : null;
                if ($suppliertype== $this->helper::NONCATALOG_RFQ_TYPE_PRIVATE && is_array($marketplace_suppler_ids)) {
                    foreach ($marketplace_suppler_ids as $key => $vendorId) {
                        $rfqMktVendor =  $this->_rfqVendorFactory->create();
                        $vendor_type = 1;
                        $vendor_id = $vendorId;
                        $is_emailsend = 0;
                        $rfqMktVendor->setData('rfq_id', $rfq_id);
                        $rfqMktVendor->setData('vendor_type', $vendor_type);
                        $rfqMktVendor->setData('vendor_id', $vendor_id);
                        $rfqMktVendor->setData('is_emailsend', $is_emailsend);
                        $rfqMktVendor->save();
                    }
                }
            }

            //Create RFQ Non-Market Place Vendor
            $nonmarket_supplist_data = (isset($postData['nonmarket_supplist_data'])) ? '[' . $postData['nonmarket_supplist_data'] . ']' : '';
            if ($nonmarket_supplist_data!='') {

                $nonmarketsupplistArray = json_decode($nonmarket_supplist_data, true);
                if (is_array($nonmarketsupplistArray)) {
                    foreach ($nonmarketsupplistArray as $key => $data) {
                        //$supplierData = json_decode($data,true);
                        $supplierData = $data;
                        $result = $this->createNonMarketPlacevendos($supplierData, $rfq_id);
                    }
                }
            }

            // Raise Event
            $this->_eventManager->dispatch(
                'noncatalogrequesttoquote_submit_success',
                ['noncatalogrequesttoquote_controller' => $this, 'noncatalogRfq' => $nonCatalogQuoteModel]
            );

            // Send Admin Email
            $label = $this->quoteStatus->getOptionText($status);
            $totals['subtotal'] = $nonCatalogQuoteModel->getRfqTotalPrice();
            ///$totals['shipping'] = $nonCatalogQuoteModel->getShippingAmount();
            $totals['grandtotal'] = $nonCatalogQuoteModel->getRfqTotalPrice();
            //$customerEmail = $customerData['email'];
            //$customerName = $postData['name'];
            $customerEmail =   $this->session->getCustomer()->getEmail();
            $customerName =   $this->session->getCustomer()->getfirstname();
            $arrrfqtype = $this->helper->getRFQuoteTypes();
            $rfqtype =  $arrrfqtype[$rfq_type];
            $companyName = $postData['companyname'];
            $template = 'noncatalogrfq_submit_email_template';
            $template_variables = [
                'quote_id' => '#' . $nonCatalogQuoteModel->getQuoteIncrementId(),
                'quote_status' => $label,
                'items' => $item_info,
                'totals' => $totals,
                'name' => $customerName,
                'company_name'=> $company_name,
                'rfqtype' => $rfqtype
            ];
            //Send Email to Admin
            $this->helper->sendAdminEmail($template, $template_variables, $customerEmail);

            //Send mail to Customer for successfull RFQ Submut
            $template = 'noncatalog_rfqsubmit_success_email_template';
            $email =   $this->session->getCustomer()->getEmail();
            $name =   $this->session->getCustomer()->getfirstname();
            $template_variables = [
             'name' => $name,
             'quote_id' => '#' . $nonCatalogQuoteModel->getQuoteIncrementId(),
             'quote_status' => $label
              ];
            $this->helper->sendEmail($template, $template_variables, $email);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('noncatalogrequesttoquote/index/index');
        }
        $this->messageManager->addSuccessMessage(__('You have successfully submitted your Quote.'));
        if (!(int)$this->isApproveCustomer()) {
            $this->session->destroy();
            return $this->resultRedirectFactory->create()
                 ->setPath('customer/account/login');
        } else {
            return $this->_redirect('noncatalogrequesttoquote/customer/quotes/');
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

    private function getCustomerId()
    {
        return $this->session->getCustomer()->getId();
    }

    private function getlevelTwoCategoryId($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $currentCategoryLevel = $category->getLevel();
        $parentId = $category->getParentId();
        $levelTwocategoryId = $category->getId();
        while ($currentCategoryLevel!=2) {
            $category = $this->_categoryFactory->create()->load($parentId);
            $parentId = $category->getParentId();
            $currentCategoryLevel = $category->getLevel();
            $levelTwocategoryId = $category->getId();
        }
        return $levelTwocategoryId;
    }
}
