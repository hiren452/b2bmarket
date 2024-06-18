<?php

namespace Matrix\Auction\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class RequestToJoin extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var
     */
    public $_resultForwardFactory;

    protected $jsonResultFactory;

    protected $inlineTranslation;

    protected $storeManager;

    protected $scopeConfig;

    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'auction_entry_1/standard/request_template';

    /**
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $session;
        $this->jsonResultFactory = $jsonResultFactory;

        $this->inlineTranslation         = $inlineTranslation;
        $this->transportBuilder          = $transportBuilder;
        $this->storeManager              = $storeManager;
        $this->scopeConfig               = $scopeConfig;
        $this->_vendorFactory            = $vendorFactory;
        $this->_customerFactory          = $customerFactory;
        $this->productFactory            = $productFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        if(!$this->session->isLoggedIn()) {
            $this->session->setAfterAuthUrl($this->_url->getCurrentUrl());
            $this->session->authenticate();
        }

        $vendorId = $this->getRequest()->getParam('vendor_id');

        $vendor = $this->_vendorFactory->create()->load($vendorId);
        $customer = $this->getCustomerById($this->session->getCustomerId());
        $product_details = $this->getProductDeatils($this->getRequest()->getParam('product_id'));

        //==== Send invitations mail ======
        $templateVars = [
                        'store'        => $this->storeManager->getStore(),
                        'first_name'   => $customer->getData('firstname'),
                        'last_name'    => $customer->getData('lastname'),
                        'email'        => $customer->getData('email'),
                        'vendor_name'  => $vendor->getName(),
                        'product_name' => $product_details['product_name'],
                        'product_url'  => $product_details['product_url']
                    ];

        $this->sendInviteEmail($vendor->getEmail(), $templateVars);
        //==== End of, Send invitations mail ======

        $result = $this->jsonResultFactory->create();
        $return['msg'] = __('Request has been sent.');
        return $result->setData($return);
    }

    private function sendInviteEmail($vendor_email, $templateVars = [])
    {
        $to = $vendor_email;
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

    public function getCustomerById($id)
    {
        return $this->_customerFactory->create()->load($id);
    }

    public function getProductDeatils($product_id)
    {
        $product = $this->productFactory->create();
        $product->load($product_id);

        $arr['product_name'] = $product->getName();
        $arr['product_url']  = $product->getProductUrl();

        return $arr;
    }
}
