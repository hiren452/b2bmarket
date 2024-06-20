<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlFactory;

class Vendor extends \Magento\Framework\App\Action\Action
{

    public static $openActions = [
            'create',
            'login',
            'logoutsuccess',
            'forgotpassword',
            'forgotpasswordpost',
            'resetpassword',
            'resetpasswordpost',
            'confirm',
            'confirmation',
            'approval',
            'approvalPost',
            'checkAvailability',
            'denied',
            'noRoute'
        ];

    public $_allowedResource = true;

    protected $session;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    protected $registry;

    protected $acl;

    protected $helper;

    protected $vendor;

    /**
     * Vendor constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Ced\CsMarketplace\Model\Vendor $vendor
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Ced\CsMarketplace\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Ced\CsMarketplace\Helper\Acl $acl,
        \Ced\CsMarketplace\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->urlModel = $urlFactory;
        $this->registry = $registry;
        $this->registry->register('vendor', $this->session->getVendor());
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vendor = $vendor;
        $this->acl = $acl;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $this->_eventManager->dispatch(
            'ced_csmarketplace_predispatch_action',
            [
            'session' => $this->session,
            ]
        );
        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }
        $result = parent::dispatch($request);
        $this->session->unsNoReferer(false);
        return $result;
    }

    public function execute()
    {
    }

    /**
     * Retrieve customer session model object
     * @return Session
     */
    protected function _getSession()
    {
        return $this->session;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
     */
    protected function getAllowedActions()
    {
        return self::$openActions;
    }

    /**
     * Authenticate controller action by login customer
     * @param Vendor $action
     * @param null $loginUrl
     * @return bool|\Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate(Vendor $action, $loginUrl = null)
    {
        if (!$this->session->isLoggedIn()) {
            if ($action->getRequest()->isAjax()) {
                $this->session->setBeforeVendorAuthUrl($this->urlModel->create()->getUrl('*/vendor/', ['_secure' => true,'_current' => true]));
            } else {

                $oAuthUrl = $this->urlModel->create()->getUrl('*/*/*', ['_current'=>true]);
                if (!preg_match('/' . preg_quote('csmarketplace') . '/i', $oAuthUrl)) {
                    $oAuthUrl = $this->urlModel->create()->getUrl('csmarketplace/vendor/index', ['_current'=>true]);
                }
                $this->session->setBeforeVendorAuthUrl($oAuthUrl);
            }
            if (is_null($loginUrl)) {

                $url='csmarketplace/account/login';
                $loginUrl = $this->urlModel->create()->getUrl($url, ['_secure' => $action->getRequest()->isSecure()]);
            }
            if ($action->getRequest()->isAjax()) {
                $ajaxResponse=[];
                $ajaxResponse['ajaxExpired']= true;
                $ajaxResponse['ajaxRedirect']=$loginUrl;
                $action->getResponse()->setBody(json_encode($ajaxResponse));
                $resultJson = $this->resultJsonFactory->create();

                return $resultJson->setData($response);
            }
            $action->getResponse()->setRedirect($loginUrl);
            return false;
        }
        if ($this->session->isLoggedIn() && $this->helper->authenticate($this->session->getCustomerId())) {
            $vendor = $this->vendor->loadByCustomerId($this->session->getCustomerId());
            if ($vendor && $vendor->getId()) {
                $this->session->setVendorId($vendor->getId());
                $this->session->setVendor($vendor->getData());
                $this->_eventManager->dispatch(
                    'ced_csmarketplace_vendor_authenticate_after',
                    [
                    'session' => $this->session
                    ]
                );
            }
        }
        $this->_eventManager->dispatch(
            'ced_csmarketplace_vendor_acl_check',
            [
            'current' => $this,
            'action'  => $action,
            ]
        );
        return $this->_allowedResource;
    }
}
