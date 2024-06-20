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

namespace Ced\CsMultistepreg\Controller\Rewrite\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Approval extends \Ced\CsMarketplace\Controller\Account\Approval
{

    protected $customerRepository;

    protected $dataObjectHelper;

    public $vendor;

    public $helper;

    protected $multistepHelper;

    public $resultPageFactory;

    /**
     * Approval constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMultistepreg\Helper\Data $multistepHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsMultistepreg\Helper\Data $multistepHelper
    ) {
        $this->dataHelper = $csmarketplaceHelper;
        $this->multistepHelper = $multistepHelper;
        $this->vendor = $vendor;
        $this->resultPageFactory = $resultPageFactory;
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

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        //echo "dhasdg";
        try {

            $resultRedirect = $this->resultRedirectFactory->create();
            if ($this->_getSession()->isLoggedIn() && $this->dataHelper->authenticate($this->session->getCustomerId())) {
                $resultRedirect->setPath('*/vendor/');
                return $resultRedirect;
            }

            /* redirect to multistep registration if has not filled all the pages */
            $vendor = $this->vendor->create()->loadByCustomerId($this->session->getCustomerId());
            if ($vendor && !$vendor->getMultistepDone()) {
                $enabled = $this->multistepHelper->isEnabled();
                if ($enabled == '1') {

                    $resultRedirect->setPath('csmultistep/multistep/index', ['id'=>$vendor->getId()]);
                    return $resultRedirect;
                }

            }

            /* upto here */
            if (!$this->authenticate($this)) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
            $this->session->unsVendorId();
            $this->session->unsVendor();
            if ($this->session->isLoggedIn()) {

                $vendor = $this->vendor->create()->loadByCustomerId($this->session->getCustomerId());
                if ($vendor && $vendor->getId()) {
                    $this->session->setData('vendor_id', $vendor->getId());
                    $this->session->setData('vendor', $vendor->getData());
                }
            }
            $resultPage = $this->resultPageFactory->create();

            $resultPage->getConfig()->getTitle()->set(__('Account Approval'));
            return $resultPage;
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
    }
}
