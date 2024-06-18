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

namespace Ced\Auction\Block\Auction;

use Ced\Auction\Helper;
use Ced\Auction\Model\ResourceModel;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    protected $collection = null;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        UrlInterface $urlInterface,
        DateTime $datetime,
        Helper\ConfigData $configHelper,
        ProductRepository $productRepository,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        array $data = []
    ) {
        $this->winnerCollection = $winnerCollection;
        $this->auctionCollection = $auctionCollection;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->date = $datetime;
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
        $this->_stockItemRepository = $stockItemRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getWinningAuction()
    {

        if($this->collection!==null) {
            return $this->collection;
        }
        $id = $this->customerSession->getData('customer_id');

        $this->collection = $this->winnerCollection->create()->addFieldToFilter('customer_id', $id);

        $purchasedDay = $this->configHelper->getConfigData('auction_entry_1/standard/remove_purchase_link');
        $days = '+' . $purchasedDay . ' day';

        foreach ($this->collection as $item) {
            if ($purchasedDay) {
                $bidDate = $item->getBidDate(); // Get the bid date
                if (!empty($bidDate) && strtotime($bidDate) !== false) { // Check if bid date is not empty and is a valid date
                    $biddingDate = strtotime($bidDate); // Convert bid date to timestamp
                    $days = $purchasedDay; // Assuming $purchasedDay is defined elsewhere in your code
                    $biddingEnddate = date("Y-m-d", strtotime($days, $biddingDate)); // Calculate bidding end date
                    $now = strtotime($this->date->gmtDate()); // Get current date as timestamp
                    $nowDate = date("Y-m-d", $now); // Convert current date to Y-m-d format
                    $status = $item->getStatus();
                    if ($biddingEnddate <= $now && $status == 'not purchased') {
                        $item->setData('status', 'purchase link expired');
                        $item->save();
                    }
                }
            }

        }

        if($this->collection) {
            $this->collection = $this->winnerCollection->create()->addFieldToFilter('customer_id', $id);
        }
        return $this->collection;
    }

    /**
     * @return string
     */
    public function getUrlInterface()
    {

        return $this->urlInterface->getUrl();
    }

    public function getProductName($productId)
    {
        return $this->productRepository->getById($productId)->getName();
    }
    public function getProduct($productId)
    {
        return $this->productRepository->getById($productId);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getWinningAuction()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'reward.history.pager'
            )->setCollection(
                $this->getWinningAuction()
            );
            $this->setChild('pager', $pager);
            $this->getWinningAuction()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function checkProductAvailability($productid)
    {

        $product = $this->productRepository->getById($productid);
        if($product) {
            $qty = $this->productRepository->getById($productid)->getExtensionAttributes()->getStockItem()->getIsInStock();
        }

        if(!$qty || empty($product)) {
            return true;
        } else {
            return false;
        }

    }
}
