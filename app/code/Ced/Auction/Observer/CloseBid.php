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

use Ced\Auction\Helper;
use Ced\Auction\Model\Auction;
use Ced\Auction\Model\ResourceModel;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class CloseBid implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * CloseBid constructor.
     *
     * @param Session                          $session
     * @param Order                            $order
     * @param ResourceModel\Auction\Collection $auctionResource
     * @param ResourceModel\Winner\Collection  $winnerResource
     */
    public function __construct(
        Session $session,
        Order $order,
        ResourceModel\Auction\CollectionFactory $auctionResource,
        ResourceModel\Winner\CollectionFactory $winnerResource,
        Helper\SendEmail $emailHelper,
        Helper\ConfigData $configHelper,
        Auction $auction
    ) {
        $this->customerSession = $session;
        $this->order = $order;
        $this->auctionResource = $auctionResource;
        $this->winnerResource = $winnerResource;
        $this->emailHelper = $emailHelper;
        $this->configHelper = $configHelper;
        $this->auction = $auction;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {

        $order = $observer->getEvent()->getOrderIds();
        $orderDatas = $this->order->load($order)->getAllItems();
        foreach ($orderDatas as $orderData) {
            $id = $orderData->getProductId();

            $auction = $this->auctionResource->create()->addFieldToFilter('product_id', $id)
                ->addFieldToFilter('status', 'closed')
                ->getLastItem();

            $auction->setData('sellproduct', 'yes');
            $auction->save();

            if($this->customerSession->getCustomerId()) {
                $winner = $this->winnerResource->create()->addFieldToFilter('product_id', $id)
                    ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
                    ->addFieldToFilter('status', 'not purchased');

                $winner->setDataToAll('status', 'order placed');
                $winner->save();
            }

            /*if($this->customerSession->getCustomerId()) {
                $enableMail = json_decode($this->configHelper
                    ->getConfigData('auction_entry_1/email/email_winner_purchase'), true);
                if ($enableMail) {
                    $this->emailHelper->sendPurchaseMail($this->customerSession->getCustomerId(), $id);
                }

                $enableAdminMail = json_decode($this->configHelper
                    ->getConfigData('auction_entry_1/email/email_admin'), true);
                if ($enableAdminMail) {
                    $this->emailHelper->sendPurchaseMailtoAdmin($this->customerSession->getCustomerId(), $id);
                }
            }*/
        }
    }
}
