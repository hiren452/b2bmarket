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

use Ced\Auction\Helper\ConfigData;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        ConfigData $configData
    ) {
        $this->configData = $configData;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if(json_decode($this->configData->getConfigData('auction_entry_1/standard/standard_enable'), true)) {
            $resultPage = $this->resultPageFactory->create();
            // $resultPage->setActiveMenu('Ced_Auction::auction');

            $resultPage->getConfig()->getTitle()->prepend(__('Auction'));
            $resultPage->getConfig()->getTitle()->prepend(__('View Bids'));

            return $resultPage;
        } else {
            $this->messageManager->addErrorMessage('Auction is disabled from configurations. Enable module to add new auction');
            return $resultRedirect->setPath('auction/addAuction/index');
        }
    }
    public function _isAllowed()
    {
        return true;
    }
}
