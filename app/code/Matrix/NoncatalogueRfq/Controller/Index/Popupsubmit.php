<?php

namespace Matrix\NoncatalogueRfq\Controller\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\NoncatalogueRfq\Helper\Data;

class Popupsubmit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @var Session
     */
    protected $session;

    protected $noncatrfqsession;

    /**
     *
     * @var UrlInterface
     */
    protected $urlInterface;

    protected $_coreRegistry = null;

    /**
     * @var RfqTemplateFactory
     */
    protected $_rfqtemplateFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    protected $sessionManagerInterface;

    protected $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session     $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Matrix\NoncatalogueRfq\Model\Session $noncatrfqSession,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqtemplateFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        AccountManagementInterface $customerAccountManagement,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        \Magento\Framework\Session\SessionManagerInterface $sessionManagerInterface,
        Data $helper
    ) {

        $this->resultPageFactory = $pageFactory;
        $this->session = $session;
        $this->noncatrfqsession = $noncatrfqSession;
        $this->urlInterface = $urlInterface;
        $this->formKeyValidator = $formKeyValidator;
        $this->helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_rfqtemplateFactory = $rfqtemplateFactory;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->storeManager = $storeManager;
        $this->sessionManagerInterface= $sessionManagerInterface;

        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $postdata = $this->getRequest()->getParams();
        $customerExist = $this->emailExistOrNot($postdata['email-rfqpopup']);

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            // invalid request
            $resultRedirect = $this->resultRedirectFactory->create(ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addErrorMessage(__('Invalid request.'));
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        //
        if (!$this->helper->isEnable()) {
            $this->_redirect('customer/account/login');
            return;
        }
        if ($this->session->isLoggedIn()) {
            $this->_redirect('noncatalogrequesttoquote/index/index');
            return;
        }

        if (!$this->session->isLoggedIn()) {
            $postData = $this->getRequest()->getParams();
            $jsonPostData = json_encode($postData);
            $this->noncatrfqsession->setData('nonCatrRqPopupPostData', $jsonPostData);
            $redirectUrl = $this->urlInterface->getUrl('noncatalogrequesttoquote/index/index');
            $this->messageManager->addErrorMessage(__('Please login first before creating RFQ Template.'));
            $this->session->setAfterAuthUrl($redirectUrl);
            if ($customerExist) {
                $redirect_url = $this->urlInterface->getUrl('customer/account/login', ['referer' => base64_encode($redirectUrl)]);
            } else {
                $redirect_url = $this->urlInterface->getUrl('customer/account/create', ['referer' => base64_encode($redirectUrl)]);
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($redirect_url);
            return $resultRedirect;

        }
    }

    /**
     *
     * @return bool
     */
    public function emailExistOrNot($email)
    {
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $isEmailNotExists = $this->customerAccountManagement->isEmailAvailable($email, $websiteId);
        return !$isEmailNotExists;
    }
}
