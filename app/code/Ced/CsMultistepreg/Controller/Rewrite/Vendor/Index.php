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

namespace Ced\CsMultistepreg\Controller\Rewrite\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Ced\CsMarketplace\Controller\Vendor\Index
{

    protected $session;

    protected $resultPageFactory;

    protected $helper;

    protected $vendor;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsMultistepreg\Helper\Data $helper
     */

    public function __construct(
        Context                                          $context,
        \Magento\Framework\View\Result\PageFactory       $resultPageFactory,
        Session                                          $customerSession,
        UrlFactory                                       $urlFactory,
        \Magento\Framework\Registry                      $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data                   $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl                    $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory           $vendor,
        \Ced\CsMultistepreg\Helper\Data                  $helper
    ) {
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->vendor = $vendor;
        $this->helper = $helper;
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $vendor = $this->session->getVendor();
        if ((isset($vendor['multistep_done']) && !$vendor['multistep_done']) || !isset($vendor['multistep_done'])) {
            if ($this->helper->isEnabled()) {
                $this->_redirect('csmultistep/multistep/index', ['id' => $this->session->getVendorId()]);
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Dashboard'));
        return $resultPage;
    }
}
