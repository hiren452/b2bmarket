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
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Controller\Po;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data as CsMarketplaceHelperData;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsRfq\Helper\Data;
use Ced\RequestToQuote\Model\PoFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class ViewPo extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param CsMarketplaceHelperData $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param Registry $coreRegistry
     * @param PoFactory $poFactory
     * @param Data $csRfqHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        CsMarketplaceHelperData $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        Registry $coreRegistry,
        PoFactory $poFactory,
        Data $csRfqHelper
    ) {
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
        $this->csRfqHelper = $csRfqHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->poFactory = $poFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (! $this->csRfqHelper->isVendorRfqEnable()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        if ($id = $this->getRequest()->getParam('id')) {
            $currentPo = $this->poFactory->create()->load($id);
            if ($currentPo && $currentPo->getId()) {
                $this->_coreRegistry->register('current_po', $currentPo);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend('#' . $currentPo->getPoIncrementId());
                return $resultPage;
            } else {
                $this->messageManager->addErrorMessage(__('This Po no longer exist.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('rfq/po/index');
    }
}
