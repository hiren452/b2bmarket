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

namespace Ced\Auction\Controller\StartBid;

use Ced\Auction\Model\AuctionFactory;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Framework\App\Action\Context;

class Updatebid extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * ConfigData constructor.
     *
     * @param Context              $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CollectionFactory $bidDetails,
        \Magento\Framework\Pricing\Helper\Data $helper,
        AuctionFactory $auctionFactory,
        \Ced\Auction\Helper\ConfigData $configHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->bidDetails = $bidDetails;
        $this->helper = $helper;
        $this->auctionFactory = $auctionFactory;
        $this->configHelper = $configHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }
    /**
     * @return \Ced\Auction\Model\ResourceModel\BidDetails\Collection
     */
    public function execute()
    {
        $currentBidAmount = 0;
        $incrementedbid = 0;
        $auctionStartDate = $this->auctionFactory->create()->load($this->getRequest()->getPost('id'));
        $this->_bidCollection = $this->bidDetails->create()
            ->addFieldToFilter('product_id', $this->getRequest()->getPost('id'))
            ->addFieldToFilter('status', 'bidding')
            ->setOrder('bid_price', 'DESC');
        if(!empty($this->_bidCollection->getData())) {
            $bidPrice = $this->_bidCollection->getData()[0]['bid_price'];
            if ($bidPrice != 0) {
                $currentBidPrice = $this->helper->currency($bidPrice, true, false);
                $currentBidAmount = '<div class="current-bid">
                            <label>' . __("Current bid price ") . '</label>
                            <span id="currentbid">' .
                                $currentBidPrice .
                            '</span>
                        </div> <div id="error" class="current-bid"></div>';
            }

            $enableIncrementAuction = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/increment_enable'), true);

            $defaultNextBid = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/increment_default_price'), true);
            $nextbid = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/increment_price'), true);
            $nextBidAmount = [];
            $nextAmount = 0;
            if ($enableIncrementAuction) {
                if ($nextbid != 0 || $defaultNextBid != 0) {
                    foreach ($nextbid as $bid) {
                        $currentBid = $bidPrice;
                        if ($currentBid >= $bid['pricefrom'] && $currentBid <= $bid['priceto']) {
                            array_push($nextBidAmount, ['pricefrom' => $bid['pricefrom'], 'priceto' => $bid['priceto'], 'incrementedprice' => $bid['incrementedprice']]);
                        }
                    }
                    $incrementedbid = '<div class="increment-bid" id="nextbid">';
                    if ($bidPrice != 0 && count($nextBidAmount) != 0) {
                        $nextAmount = $bidPrice + $nextBidAmount[0]['incrementedprice'];
                        $nextAmount = $this->helper->currency($nextAmount, true, false);
                        $incrementedbid .= '<label>' . __('Enter next bid or more') . '</label>
                        <span id="nextbidamount">' . $nextAmount . '</span>';
                    }
                    if ($bidPrice != 0 && count($nextBidAmount) == 0) {
                        $nextAmount = $bidPrice + $defaultNextBid;
                        $incrementedbid .= '<label>' . __('Enter next bid or more') . '</label>
                        <span id="nextbidamount">$' . $nextAmount . '.00</span>';
                    }
                }
                $incrementedbid .= '</div>';
            }
        }
        $json_encode = ['currentbid' => $currentBidAmount, 'incrementedbid' => $incrementedbid];
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($json_encode);
        return $jsonResult;
        //echo json_encode(array('currentbid' => $currentBidAmount, 'incrementedbid' => $incrementedbid));
    }
}
