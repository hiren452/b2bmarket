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

namespace Ced\Auction\Block\Adminhtml\Winner;

use Ced\Auction\Model\ResourceModel;
use Magento\Backend\Block\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;

class Details extends Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * Details constructor.
     *
     * @param Template\Context                        $context
     * @param Session                                 $customerSession
     * @param ResourceModel\Winner\CollectionFactory  $winnerCollection
     * @param ResourceModel\Auction\CollectionFactory $auctionCollection
     * @param array                                   $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        ResourceModel\Winner\CollectionFactory $winnerCollection,
        ResourceModel\Auction\CollectionFactory $auctionCollection,
        ResourceModel\BidDetails\CollectionFactory $bidDetailsCollection,
        UrlInterface $urlInterface,
        Http $request,
        array $data = []
    ) {
        $this->request = $request;
        $this->winnerCollection = $winnerCollection;
        $this->auctionCollection = $auctionCollection;
        $this->bidDetailsCollection = $bidDetailsCollection;
        $this->urlInterface = $urlInterface;
        parent::__construct($context, $data);
    }

    /**
     * winner details
     *
     * @return mixed
     */
    public function getWinnerDetails()
    {
        $id = $this->request->getParam('id');
        $winner = $this->winnerCollection->create()->addFieldToFilter('id', $id)->getData();
        return $winner;
    }

    /**
     * Details of auction won by the customer
     *
     * @return array
     */
    public function getWinnerAuctionDetails()
    {
        $id = $this->request->getParam('id');
        $winner = $this->winnerCollection->create()->addFieldToFilter('id', $id)->getFirstItem();
        $productId = $winner->getProductId();
        $auction = $this->auctionCollection->create()->addFieldToFilter('product_id', $productId)->getData();
        return $auction;
    }

    /**
     * Bidding details of auction won by the customer
     *
     * @return array
     */
    public function getWinnerBidDetails()
    {
        $id = $this->request->getParam('id');
        $winner = $this->winnerCollection->create()->addFieldToFilter('id', $id)->getFirstItem();
        $productId = $winner->getProductId();
        $customerId = $winner->getCustomerId();
        $bidDetails = $this->bidDetailsCollection->create()->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('customer_id', $customerId)->getData();
        return $bidDetails;
    }

    public function getUrlInterface()
    {
        return $this->urlInterface->getUrl();
    }

}
