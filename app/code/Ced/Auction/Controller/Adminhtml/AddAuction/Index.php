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
 * @category  Ced
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Controller\Adminhtml\AddAuction;

use Magento\Backend\App\Action;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ced\Auction\Helper\Data $helper,
        \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollection
    ) {
        $this->auctionCollection = $auctionCollection;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function _isAllowed()
    {
        return true;
    }

    /**
     * Edit grid record
     *
     * @return                                  \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $auctionData = $this->auctionCollection->create();
        if($auctionData) {
            foreach ($auctionData as $auctions) {
                if($auctions->getProductId() == null) {
                    $auctions->delete();
                }
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Auction');
        $resultPage->addBreadcrumb(__('CMS'), __('CMS'));
        $resultPage->addBreadcrumb(__('Add'), __('New Auction'));
        $resultPage->getConfig()->getTitle()->prepend(__('Add New Auction'));
        $this->helper->closeAuction();
        return $resultPage;
    }
}
