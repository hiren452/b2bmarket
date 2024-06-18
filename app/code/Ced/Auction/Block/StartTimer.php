<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 11/3/19
 * Time: 11:00 AM
 */

namespace Ced\Auction\Block;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class StartTimer extends Template
{
    protected $collection = null;

    public $imageBuilder;

    public $timezone;

    public function __construct(
        TimezoneInterface $timezone,
        Template\Context $context,
        ProductRepository $productCollection,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        ResourceModel\BidDetails\CollectionFactory $bidCollection,
        StoreManagerInterface $storeManager,
        DateTime $datetime,
        ConfigData $configHelper,
        Context $productContext
    ) {

        $this->timezone = $timezone;
        $this->date = $datetime;
        $this->storeManager = $storeManager;
        $this->bidCollection = $bidCollection;
        $this->auctionCollection = $auctionCollection;
        $this->productCollection = $productCollection;
        $this->configHelper = $configHelper;
        $this->imageBuilder = $productContext->getImageBuilder();
        parent::__construct($context);

    }

    public function getStartTime()
    {

        $auctionRunning = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $this->getProductId())
            ->addFieldToFilter('status', 'not started')->getFirstItem();

        return $auctionRunning->getStartDatetime();
    }

    public function getId()
    {

        return $this->getProductId();
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimeZone()
    {
        return $this->timezone;
    }

    public function getDateTimeCurrent()
    {
        return $this->timezone->date()
            ->format('Y-m-d H:i:s');
    }
}
