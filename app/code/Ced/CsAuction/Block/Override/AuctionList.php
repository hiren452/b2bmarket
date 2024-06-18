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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\Override;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class AuctionList extends Template
{

    protected $collection = null;

    public $imageBuilder;

    protected $configHelper;

    protected $timezoneInterface;

    /**
     * AuctionList constructor.
     *
     * @param Template\Context                           $context
     * @param array                                      $data
     * @param ProductRepository                          $productCollection
     * @param ResourceModel\Auction\CollectionFactory    $auctionCollection
     * @param ResourceModel\BidDetails\CollectionFactory $bidCollection
     * @param StoreManagerInterface                      $storeManager
     * @param DateTime                                   $datetime
     * @param ConfigData                                 $configHelper
     */
    public function __construct(
        Template\Context $context,
        ProductRepository $productCollection,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        ResourceModel\BidDetails\CollectionFactory $bidCollection,
        StoreManagerInterface $storeManager,
        DateTime $datetime,
        ConfigData $configHelper,
        Context $productContext,
        TimezoneInterface $timezoneInterface,
        Auction $auction,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\Auction\Helper\SendEmail $emailHelper,
        \Ced\Auction\Model\Winner $winner,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->bidCollection = $bidCollection;
        $this->auctionCollection = $auctionCollection;
        $this->productCollection = $productCollection;
        $this->configHelper = $configHelper;
        $this->imageBuilder = $productContext->getImageBuilder();
        $this->datetime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->auction = $auction;
        $this->customerSession = $customerSession;
        $this->emailHelper = $emailHelper;
        $this->winner = $winner;
        $this->vproduct = $vproducts;
        parent::__construct($context, $data);

    }

    public function getAuctionProduct()
    {
        $day = $this->configHelper->getConfigData('auction_entry_1/standard/remove_auction_product');

        if ($this->collection !== null) {
            return $this->collection;
        }

        $this->collection = $this->auctionCollection->create();

        if ($day) {
            $days = '-' . $day . ' day';
            $fromdate = strtotime($this->datetime->gmtDate());
            $enddate = date("Y-m-d H:i:s", strtotime($days, $fromdate));
            $this->collection->getSelect()->where("status in('processing','not started') or (status in('closed') and end_datetime >= '" . $enddate . "')");
        } else {
            $this->collection->addFieldToFilter('status', ['neq'=> 'closed']);
        }
        return $this->collection->setOrder('id', 'DESC');
    }

    public function changeStatus()
    {
        $date = $this->datetime->gmtDate();
        $currenttime = $this->timezoneInterface
            ->date(new \DateTime($date))
            ->format('Y-m-d H:i:s');

        $collection = $this->auctionCollection->create();
        foreach ($collection as $collections) {
            $starttime = $collections->getStartDatetime();
            $endtime = $collections->getEndDatetime();
            if($starttime <= $currenttime) {
                if($collections->getStatus() == 'not started') {
                    $collections->setStatus('processing');
                    $collections->save();
                }
            }
        }

        return $collection;
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function getAuctionProductUrl($id)
    {
        try {
            return $this->productCollection->getById($id)->getProductUrl();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return ResourceModel\Auction\Collection
     */
    public function getClosedAuctionProduct()
    {
        $auctionClosed = $this->auctionCollection->create()->addFieldToFilter('status', 'closed');

        return $auctionClosed;
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getAuctionProduct()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'reward.history.pager'
            )
                ->setShowPerPage(true)->setCollection(
                    $this->getAuctionProduct()
                );
            $this->setChild('pager', $pager);
            $this->getAuctionProduct()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getImage($product, $imageId, $attributes = [])
    {

        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param  $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        return $this->productCollection->getById($productId);
    }

    public function updateClosedAuction()
    {
        $auctionRunning = $this->auctionCollection->create()
            ->addFieldToFilter('status', ['processing', 'not started']);
        $auctionclosed = $auctionRunning->getData();

        if (count($auctionclosed) == 0) {
            return false;
        }

        foreach ($auctionclosed as $auctions) {

            $endTime = $auctions['end_datetime'];

            $date = $this->datetime->gmtDate();
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');

            if ($endTime <= $currenttime) {
                $auctionset = $this->auctionCollection->create()
                    ->addFieldToFilter('product_id', $auctions['product_id'])
                    ->addFieldToFilter('status', ['processing', 'not started'])->getLastItem();

                $auctionset->setData('status', 'closed');
                $auctionset->setData('product_sold', $endTime);
                $auctionset->save();

                $bidExist = $this->bidCollection->create()->addFieldToFilter('product_id', $auctions['product_id'])
                    ->addFieldToFilter('status', 'bidding');

                if (count($bidExist) != 0) {
                    $bid = $this->bidCollection->create()->addFieldToFilter('product_id', $auctions['product_id'])
                        ->addFieldToFilter('status', 'bidding')
                        ->setOrder('bid_price', 'ASC')->getLastItem();

                    $bid->setStatus('won');
                    $bid->save();

                    $allWinnerData = $this->bidCollection->create()
                        ->addFieldToFilter('product_id', $auctions['product_id'])
                        ->addFieldToFilter('status', 'bidding');
                    $allWinnerData->setDataToAll('status', 'biddingClosed');
                    $allWinnerData->save();

                    $winnerData = [];
                    $winnerData['product_id'] = $auctions['product_id'];
                    $winnerData['customer_id'] = $bid['customer_id'];
                    $winnerData['customer_name'] = $bid['customer_name'];
                    $winnerData['auction_price'] = $auctions['max_price'];
                    $winnerData['bid_date'] = $endTime;
                    $winnerData['status'] = 'not purchased';
                    $winnerData['winning_price'] = $bid['bid_price'];
                    $winnerData['add_to_cart'] = false;

                    if ($this->vproduct->getVendorIdByProduct($auctions['product_id'])) {
                        $winnerData['vendor_id'] = $this->vproduct->getVendorIdByProduct($this->getRequest()->getParam('productId'));
                    }

                    $this->winner->setData($winnerData);
                    $this->winner->save();

                    $enableMail = json_decode(
                        $this->configHelper->getConfigData(
                            'auction_entry_1/standard/email_winner'
                        ),
                        true
                    );
                    if ($enableMail) {
                        $this->emailHelper->sendMailtoWinner(
                            $this->customerSession->getCustomerId(),
                            $auctions['product_id']
                        );
                    }
                }

                return false;
            }
        }
    }

    public function getAuctionEnable()
    {
        return $this->configHelper->getConfigData('auction_entry_1/standard/standard_enable');
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimeZone()
    {
        return $this->timezoneInterface;
    }

    public function getDateTimeCurrent()
    {
        return $this->timezoneInterface->date()
            ->format('Y-m-d H:i:s');
    }
}
