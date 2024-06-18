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
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Controller\AuctionList;

use Ced\Auction\Helper\ConfigData;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

class ProductList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var
     */
    public $_resultForwardFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ConfigData $configData,
        UrlInterface $urlInterface,
        \Ced\Auction\Model\ResourceModel\Auction\CollectionFactory $auctionCollection
    ) {
        $this->configData = $configData;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlInterface = $urlInterface;
        $this->auctionCollection = $auctionCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
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

        if(!$this->configData->getConfigData('auction_entry_1/standard/standard_enable')) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $url = $this->urlInterface->getUrl();
            return $resultRedirect->setPath($url);
        }
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
