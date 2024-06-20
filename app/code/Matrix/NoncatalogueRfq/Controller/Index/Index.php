<?php

namespace Matrix\NoncatalogueRfq\Controller\Index;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Matrix\NoncatalogueRfq\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
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

    protected $customerModelFactory;

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
        CustomerFactory $customerModelFactory,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqtemplateFactory,
        Registry $coreRegistry,
        Data $helper
    ) {

        $this->resultPageFactory = $pageFactory;
        $this->session = $session;
        $this->customerModelFactory = $customerModelFactory;
        $this->noncatrfqsession = $noncatrfqSession;
        $this->urlInterface = $urlInterface;
        $this->helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_rfqtemplateFactory = $rfqtemplateFactory;
        return parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->helper->isEnable()) {
            $this->_redirect('customer/account/login');
            return;
        }

        if (! $this->session->isLoggedIn()) {
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
        $this->_initRfqPopupMiniFormdate();
        $rfqTemplate = $this->_initRfqTemplate();

        if ($this->isApproveCustomer()==0) {
            //$this->noncatrfqsession->unsetData('nonCatrRqPopupPostData');
            //$this->noncatrfqsession->setData('nonCatrRqPopupPostData', '');
            $this->messageManager->addErrorMessage(__('Do not  refresh the page. You are allowed to post RFQ only.'));
        } else { //Check Non-catalog RFQ Limit
            $customerId =  $this->session->getCustomer()->getId();
            if (!$this->helper->isAllowNewRfq($customerId)) {
                $redirectUrl = $this->urlInterface->getUrl('noncatalogrequesttoquote/customer/quotes/');
                $this->messageManager->addErrorMessage(__('You are not allow to create new Non-catalog RFQ.'));
                $this->_redirect('noncatalogrequesttoquote/customer/quotes/');
                //$resultRedirect = $this->resultRedirectFactory->create();
                //$resultRedirect->setPath($redirectUrl);
                return $resultRedirect;
            }
        }
        return $this->resultPageFactory->create();
    }

    protected function _initRfqPopupMiniFormdate()
    {
        $noncatRqpPopupPostData = $this->noncatrfqsession->getData('nonCatrRqPopupPostData');//get RFQ Popup  mini Form Data from Session
        if (isset($noncatRqpPopupPostData) && $noncatRqpPopupPostData!='') {
            $this->_coreRegistry->register('matrix_rfqpopupfrom', $noncatRqpPopupPostData);
        } else {
            $this->_coreRegistry->register('matrix_rfqpopupfrom', null);
        }

        return;
    }

    protected function _initRfqTemplate()
    {
        //$templateId = (int)$this->getRequest()->getParam('id', false);
        $templateId = $this->getRequest()->getParam('id');
        try {

            if ($templateId) {
                $rfqTemplate = $this->_rfqtemplateFactory->create()->load($templateId);
                $this->_coreRegistry->register('matrix_currentrfqtemplate', $rfqTemplate);
                $this->_eventManager->dispatch(
                    'matrix_noncatalog_rfqtemplate_init_after',
                    ['rfqtemplate' => $rfqTemplate, 'controller_action' => $this]
                );
            } else {
                $this->_coreRegistry->register('matrix_currentrfqtemplate', null);
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultPageFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('customer/account/index');
            return $resultRedirect;
        }
        return;
    }

    private function isApproveCustomer()
    {
        $customerId = $this->session->getCustomer()->getId();
        $customer = $this->customerModelFactory->create()->load($customerId);
        $isApproveCustomer =  0;
        if ($customer->getIsApprove()) {
            $isApproveCustomer = 1;
        }
        return $isApproveCustomer;
    }
}
