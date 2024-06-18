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

namespace Ced\Auction\Plugin;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AddtoCartPlugin extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        ConfigData $configData,
        CollectionFactory $auctionCollection,
        Auction $auction,
        DateTime $datetime
    ) {
        $this->configData = $configData;
        $this->auctionCollection = $auctionCollection;
        $this->auction = $auction;
        $this->datetime = $datetime;
    }

    public function afterisSaleable($subject, $result)
    {

        $enableAuction = $this->configData->getConfigData('auction_entry_1/standard/standard_enable');
        $day = $this->configData->getConfigData('auction_entry_1/standard/remove_auction_product');

        if($enableAuction) {

            $auction = $this->auctionCollection->create()->addFieldToFilter('product_id', $subject->getData('entity_id'))
                ->addFieldToFilter('sellproduct', 'no')
                ->addFieldToFilter('status', ['processing', 'not started']);

            foreach ($auction as $auctions) {

                return false;
            }

            if ($day) {
                $days = '-' . $day . ' day';
                $fromdate = strtotime($this->datetime->gmtDate());
                $enddate = date("Y-m-d H:i:s", strtotime($days, $fromdate));
                $auction = $this->auctionCollection->create()->addFieldToFilter('product_id', $subject->getData('entity_id'))
                    ->addFieldToFilter('sellproduct', 'no')
                    ->addFieldToFilter('status', 'closed')
                    ->addFieldToFilter('end_datetime', [['gteq' => $enddate], ['null' => true]]);

            } else {
                $auction = $this->auctionCollection->create()->addFieldToFilter('product_id', $subject->getData('entity_id'))
                    ->addFieldToFilter('sellproduct', 'no')
                    ->addFieldToFilter('status', ['processing','not started']);
            }
            foreach ($auction as $auctions) {
                return false;
            }
        }
        return $result;

    }
}
