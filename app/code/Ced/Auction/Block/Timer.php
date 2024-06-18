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

namespace Ced\Auction\Block;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Timer extends Template
{
    protected $collection = null;

    public $imageBuilder;

    protected $timezone;

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

    public function getEndTime()
    {

        $auctionRunning = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $this->getProductId())
            ->addFieldToFilter('status', 'processing')->getFirstItem();
        return $auctionRunning->getEndDatetime();
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
