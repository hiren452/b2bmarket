<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRfq
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Controller\Po;

class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     * @param \Ced\CsRfq\Helper\Data $csRfqHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Ced\CsRfq\Helper\Data $csRfqHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper
    ) {
        $this->csRfqHelper = $csRfqHelper;
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
     * @return void
     */
    public function execute()
    {
        if (! $this->csRfqHelper->isVendorRfqEnable()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Po'));
        return $resultPage;
    }
}
