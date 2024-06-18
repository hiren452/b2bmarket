<?php

namespace Matrix\CsAuction\Controller\AuctionList;

class SavePrivateAuction extends \Ced\CsMarketplace\Controller\Vendor
{

    protected $messageManager;

    protected $privateAuctionFactory;

    protected $inlineTranslation;

    protected $storeManager;

    protected $scopeConfig;

    protected $productFactory;

    protected $auctionFactory;

    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'auction_entry_1/standard/template';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Matrix\CsAuction\Model\PrivateAuctionFactory $privateAuctionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\Auction\Model\AuctionFactory $auctionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->privateAuctionFactory     = $privateAuctionFactory;
        $this->messageManager            = $messageManager;

        $this->inlineTranslation         = $inlineTranslation;
        $this->transportBuilder          = $transportBuilder;
        $this->storeManager              = $storeManager;
        $this->scopeConfig               = $scopeConfig;
        $this->productFactory            = $productFactory;
        $this->auctionFactory            = $auctionFactory;
        $this->_customerFactory          = $customerFactory;

        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    public function execute()
    {

        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        if (!$this->getRequest()->isPost()) {
            return false;
        }

        $data = $this->getRequest()->getParams();

        $model = $this->privateAuctionFactory->create();
        $model = $model->load($this->getRequest()->getParam('auction_id'), 'auction_id');
        $product_details = $this->getProductDeatils($this->getRequest()->getParam('auction_id'));

        $customer_emails = $this->getRequest()->getParam('customer_emails');
        $first_name      = $this->getRequest()->getParam('first_name');
        $last_name       = $this->getRequest()->getParam('last_name');
        $company_name    = $this->getRequest()->getParam('company_name');

        $existing_emails = [];
        if(!empty($model->getCustomerEmails())) {
            $existing_emails = array_column(json_decode($model->getCustomerEmails(), true), 'email');
        }

        $mail_ids = '';
        $arr = [];

        foreach ($customer_emails as $key => $email) {
            if(!empty($email) && !in_array($email, $existing_emails)) {
                $arr[$key]['email']        = $email;
                $arr[$key]['company_name'] = $company_name[$key];
                $arr[$key]['first_name']   = $first_name[$key];
                $arr[$key]['last_name']    = $last_name[$key];

                //==== Send invitations mail ======
                $templateVars = [
                                'store'        => $this->storeManager->getStore(),
                                'first_name'   => $first_name[$key],
                                'last_name'    => $last_name[$key],
                                'email'        => $email,
                                'product_name' => $product_details['product_name'],
                                'product_url'  => $product_details['product_url']
                            ];

                $this->sendInviteEmail($arr, $key, $templateVars);
                //==== End of, Send invitations mail ======
            }
        }
        if(!empty($arr)) {
            $mail_ids = json_encode($arr);
        }
        $selected_customer_arr = [];
        $selected_customers = $this->getRequest()->getParam('customer_ids', []);

        foreach ($selected_customers as $selected_customer) {
            $selected_arr = explode(',', $selected_customer);
            $selected_customer_arr[$selected_arr[0]]['first_name'] = $selected_arr[1];
            $selected_customer_arr[$selected_arr[0]]['last_name']  = $selected_arr[2];
            $selected_customer_arr[$selected_arr[0]]['email']      = $selected_arr[3];
        }

        $customer_ids = implode(',', array_keys($selected_customer_arr));

        $existing_ids = [];
        if(!empty($model->getCustomerIds())) {
            $existing_ids[] = explode(',', $model->getCustomerIds());
        }

        $arr_2 = [];
        foreach (array_keys($selected_customer_arr) as $k=>$customer_id) {
            if(!in_array($customer_id, $existing_ids)) {
                $customer = $selected_customer_arr[$customer_id];

                $arr_2[$k]['email']        = $customer['email'];
                $arr_2[$k]['company_name'] = '';
                $arr_2[$k]['first_name']   = $customer['first_name'];
                $arr_2[$k]['last_name']    = $customer['last_name'];

                //==== Send invitations mail ======
                $templateVars = [
                                'store'        => $this->storeManager->getStore(),
                                'first_name'   => $customer['first_name'],
                                'last_name'    => $customer['last_name'],
                                'email'        => $customer['email'],
                                'product_name' => $product_details['product_name'],
                                'product_url'  => $product_details['product_url']
                            ];

                $this->sendInviteEmail($arr_2, $k, $templateVars);
                //==== End of, Send invitations mail ======
            }
        }

        $model->setAuctionId($this->getRequest()->getParam('auction_id'));
        $model->setCustomerIds($customer_ids);
        $model->setVendorId($this->_getSession()->getVendorId());
        $model->setCustomerEmails($mail_ids);
        $model->setCreated(date('Y-m-d H:i:s'));
        $model->save();

        $this->_eventManager->dispatch('auction_invite_buyers', ['customer_ids' => $customer_ids]);

        $this->messageManager->addSuccess(__('Invitation sent successfully.'));
        return $this->_redirect('csauction/auctionlist/pay', ['id'=>$this->getRequest()->getParam('auction_id')]);

        //return $this->resultRedirectFactory->create()->setPath('csauction/auctionlist/index', ['_current' => true]);
    }

    private function sendInviteEmail($customer, $key, $templateVars = [])
    {
        $to = $customer[$key]['email'];
        $cc = '';
        //$to = 'goutamdutta@matrixnmedia.com';

        try {
            $this->inlineTranslation->suspend();

            $error  = false;
            $from = [
                 'name' =>  $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                 'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ];

            $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->storeManager->getStore()->getId()];

            $templateId = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {

        }
    }

    public function getTemplateId($xmlPath)
    {
        return $this->scopeConfig->getValue($xmlPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductDeatils($auction_id)
    {
        $auction = $this->auctionFactory->create();
        $auction->load($auction_id);

        $product = $this->productFactory->create();
        $product->load($auction->getProductId());

        $arr['product_name'] = $product->getName();
        $arr['product_url'] = $product->getProductUrl();

        return $arr;
    }

    public function getCustomerById($id)
    {
        return $this->_customerFactory->create()->load($id);
    }
}
