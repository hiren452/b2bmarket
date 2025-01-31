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

namespace Ced\CsMultistepreg\Controller\Multistep;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var
     */
    protected $dataObjectHelper;

    /**
     * @var \Ced\CsMultistepreg\Helper\Data
     */
    protected $multistepHelper;

    /**
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param SessionFactory $sessionFactory
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param Data $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param \Ced\CsMultistepreg\Helper\Data $multistepHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        SessionFactory $sessionFactory,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        Data $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        \Ced\CsMultistepreg\Helper\Data $multistepHelper
    ) {
        $this->_session = $customerSession;
        $this->sessionFactory = $sessionFactory;
        $this->vendorFactory = $vendor;
        $this->multistepHelper = $multistepHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     */
    public function execute()
    {
        $request = $this->_request;
        if (!$request->getParam('id')) {
            $this->_redirect('csmarketplace/vendor/index');
            return;
        }
        $vendorModel = $this->vendorFactory->create();
        $vendorData = $vendorModel->load($request->getParam('id'));

        $vEmail = $vendorModel->getEmail();
        $customerEmail = $this->sessionFactory->create()->getCustomer()->getEmail();
        if ($vEmail != $customerEmail) {
            return $this->_redirect('csmarketplace/vendor/index');
        }

        $multistep = $this->multistepHelper->checkStepData();
        if (!$multistep) {
            $vendorData->addData(['multistep_done' => 1]);
            $vendorData->save();
            return $this->_redirect('csmarketplace/vendor/index');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set('Multi Step Registration');
        return $resultPage;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_eventManager->dispatch(
            'ced_csmarketplace_predispatch_action',
            [
                'session' => $this->session,
            ]
        );
        if (!$this->getRequest()->isDispatched()) {
            \Magento\Framework\App\Action\Action::dispatch($request);
        }
        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^(' . implode('|', $this->getAllowedActions()) . ')$/i';

        $this->session->setNoReferer(true);

        $result = \Magento\Framework\App\Action\Action::dispatch($request);
        $this->session->unsNoReferer(false);
        return $result;
    }
}
