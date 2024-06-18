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

namespace Ced\Auction\Pricing\Render;

use Ced\Auction\Helper\ConfigData;
use Ced\Auction\Helper\SendEmail;
use Ced\Auction\Model;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template\Context;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    /**
     * FinalPriceBox constructor.
     *
     * @param Context                                       $context
     * @param SaleableInterface                             $saleableItem
     * @param PriceInterface                                $price
     * @param RendererPool                                  $rendererPool
     * @param array                                         $data
     * @param SalableResolverInterface|null                 $salableResolver
     * @param MinimalPriceCalculatorInterface|null          $minimalPriceCalculator
     * @param Model\ResourceModel\Auction\CollectionFactory $auctionCollection
     * @param ProductRepository                             $productCollection
     * @param Data                                          $helper
     */
    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null,
        Model\ResourceModel\Auction\CollectionFactory $auctionCollection,
        ProductRepository $productCollection,
        Data $helper,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $bidDetails,
        \Ced\Auction\Model\Winner $winner,
        ConfigData $configHelper,
        SendEmail $emailHelper,
        Session $customerSession,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->productCollection = $productCollection;
        $this->auctionCollection = $auctionCollection;
        $this->helper = $helper;
        $this->dateTime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->bidDetails = $bidDetails;
        $this->winner = $winner;
        $this->configHelper = $configHelper;
        $this->emailHelper = $emailHelper;
        parent::__construct($context, $saleableItem, $price, $rendererPool, $data, $salableResolver, $minimalPriceCalculator);
    }

    /**
     * @param  string $html
     * @return string
     */
    public function wrapResult($html)
    {
        $controller = $this->getRequest()->getControllerName();

        if ($controller == 'category') {
            $this->closeAuction($this->getSaleableItem()->getId());
            $this->startAuction($this->getSaleableItem()->getId());
            $startingPrice = $this->getStartingPrice($this->getSaleableItem()->getId());
            $url = $this->getAuctionProductUrl($this->getSaleableItem()->getId());

            if ($url) {
                $urls = '"' . $url . '"';
                $status = $this->getAuctionStatus($this->getSaleableItem()->getId());
                if ($startingPrice && $status == 'processing') {
                    $label = __("Starting Bid Amount: ");
                    $buttonLabel = __("Start Bidding");
                    return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
                        'data-role="priceBox" ' .
                        'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
                        'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
                        '><span>' . $html .
                        '</span><div class="category_timer auction_timer" id="countdown_timer' . $this->getSaleableItem()->getId() . '"><div class="auction_timer_days" id="auction_days' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_hours" id="auction_hours' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_minutes" id="auction_minutes' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_seconds" id="auction_seconds' . $this->getSaleableItem()->getId() . '"></div></div>'
                        . $this->getLayout()->createBlock("Ced\Auction\Block\Timer")->setProductId($this->getSaleableItem()->getId())->setTemplate("Ced_Auction::auctionlist/timer.phtml")->toHtml() . '<div class="auc-start-price">
                <label>' . $label . '</label><span>' . $this->helper->currency($startingPrice, true, false) . '</div></span></div>
                <a class="auction-link" href="' . $url . '">' . $buttonLabel . '</a>';
                } elseif ($startingPrice && $status == 'not started') {
                    $label = __("Starting Bid Amount: ");
                    $buttonLabel = __("Not Started");
                    return '<div class="price-box ' . $this->getData('css_classes') . '" ' .
                        'data-role="priceBox" ' .
                        'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
                        'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
                        '><span>' . $html .
                        '</span><div class="category_timer auction_timer" id="countdown_timer' . $this->getSaleableItem()->getId() . '"><div class="auction_timer_days" id="auction_days' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_hours" id="auction_hours' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_minutes" id="auction_minutes' . $this->getSaleableItem()->getId() . '"></div><div class="auction_timer_seconds" id="auction_seconds' . $this->getSaleableItem()->getId() . '"></div></div>'
                        . $this->getLayout()->createBlock("Ced\Auction\Block\StartTimer")->setProductId($this->getSaleableItem()->getId())->setTemplate("Ced_Auction::auctionlist/starttimer.phtml")->toHtml() . '<div class="auc-start-price">
                <label>' . $label . '</label><span>' . $this->helper->currency($startingPrice, true, false) . '</div></span></div>
                <a class="auction-link" href="' . $url . '">' . $buttonLabel . '</a>';
                }
            }
        }

        return parent::wrapResult($html);
    }

    /**
     * @param  $productId
     * @return bool
     */
    public function getStartingPrice($productId)
    {
        $auctionRunning = $this->auctionCollection->create()
            ->addFieldToFilter('product_id', $productId)->getFirstItem();
        if(count($auctionRunning->getData()) != false) {
            return $auctionRunning->getStartingPrice();
        }

        /*foreach ($auctionRunning as $auction)
        {
            if($auction->getProductId() == $productId)
            {
                return $auction->getStartingPrice();
            }
        }*/
        return false;
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
            //it is a super bad idea to return any logic in a catch block. This is a temporary fix by CED. James to revise the code.
            return false;
        }
    }

    public function getAuctionStatus($productId)
    {
        $auctionRunning = $this->auctionCollection->create()->addFieldToFilter('product_id', $productId)->getLastItem();
        if($auctionRunning) {
            return $auctionRunning->getStatus();
        }

        return false;
    }

    public function startAuction($productId)
    {
        $auctionRunning = $this->auctionCollection->create()->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'not started')
            ->getFirstItem();
        if($auctionRunning) {
            $startTime = $auctionRunning->getStartDatetime();
            $date = $this->dateTime->gmtDate();
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');
            if($startTime < $currenttime) {
                if(count($auctionRunning->getData()) != false) {
                    $auctionRunning->setData('status', 'processing');
                    $auctionRunning->save();
                }

            }
        }
        return false;
    }

    public function closeAuction($productId)
    {
        $auction = $this->auctionCollection->create()->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('status', 'processing')
            ->getFirstItem();
        if(count($auction->getData()) != false) {
            $endTime = $auction->getEndDatetime();

            $date = $this->dateTime->gmtDate();
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');

            if ($endTime <= $currenttime) {

                if(count($auction->getData()) != false) {
                    $auction->setData('status', 'closed');
                    $auction->setData('product_sold', $endTime);
                    $auction->save();
                }

                $bidExist = $this->bidDetails->create()->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('status', 'bidding');

                if (count($bidExist->getData()) != 0) {

                    $bid = $this->bidDetails->create()->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('status', 'bidding')
                        ->setOrder('bid_price', 'ASC')->getLastItem();
                    $bid->setStatus('won');
                    $bid->save();

                    $allWinnerData = $this->bidDetails->create()->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('status', 'bidding');

                    if(count($allWinnerData->getData()) != false) {
                        $allWinnerData->setDataToAll('status', 'biddingClosed');
                        $allWinnerData->save();

                        $winnerData = [];
                        $winnerData['product_id'] = $productId;
                        $winnerData['customer_id'] = $bid['customer_id'];
                        $winnerData['customer_name'] = $bid['customer_name'];
                        $winnerData['auction_price'] = $auction->getMaxPrice();
                        $winnerData['bid_date'] = $endTime;
                        $winnerData['status'] = 'not purchased';
                        $winnerData['winning_price'] = $bid['bid_price'];
                        $winnerData['add_to_cart'] = false;

                        $this->winner->setData($winnerData);
                        $this->winner->save();
                    }

                    $enableMail = json_decode($this->configHelper->getConfigData('auction_entry_1/standard/email_winner'), true);
                    if ($enableMail) {
                        $this->emailHelper->sendMailtoWinner(
                            $this->customerSession->getCustomerId(),
                            $productId
                        );
                    }
                }
                return false;
            }
        }
        return false;
    }
}
