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

namespace Ced\Auction\Observer;

use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DeleteProduct implements ObserverInterface
{
    public function __construct(
        CollectionFactory $product,
        ResourceModel\Auction\CollectionFactory $auction,
        ResourceModel\Winner\CollectionFactory $winner,
        ResourceModel\BidDetails\CollectionFactory $bidDetails
    ) {
        $this->product = $product;
        $this->auction = $auction;
        $this->winner = $winner;
        $this->biddetails = $bidDetails;
    }

    public function execute(Observer $observer)
    {
        $ids = $this->product->create()->getAllIds();

        $auctionCollection = $this->auction->create()->addFieldToFilter('product_id', ['nin' => $ids]);

        if ($auctionCollection) {
            foreach ($auctionCollection as $auctions) {
                $auctions->delete();
            }
        }

        $winnerCollection = $this->winner->create()->addFieldToFilter('product_id', ['nin' => $ids]);

        if ($winnerCollection) {
            foreach ($winnerCollection as $winners) {
                $winners->delete();
            }
        }

        $bidCollection = $this->biddetails->create()->addFieldToFilter('product_id', ['nin' => $ids]);

        if ($bidCollection) {
            foreach ($bidCollection as $bids) {
                $bids->delete();
            }
        }

    }
}
