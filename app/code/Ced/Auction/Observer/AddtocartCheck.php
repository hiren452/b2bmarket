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
use Ced\Auction\Model\ResourceModel\Winner\CollectionFactory;
use Magento\Checkout\Helper\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\QuoteRepository;

class AddtocartCheck implements ObserverInterface
{
    /**
     * AddtocartCheck constructor.
     *
     * @param CollectionFactory $winnerCollection
     * @param Session           $customerSession
     * @param Helper\ConfigData $configHelper
     * @param DateTime          $datetime
     * @param Cart              $cart
     * @param QuoteRepository   $quoteRepository
     * @param Helper\SendEmail  $emailHelper
     */
    public function __construct(
        CollectionFactory $winnerCollection,
        Session $customerSession,
        Helper\ConfigData $configHelper,
        DateTime $datetime,
        Cart $cart,
        QuoteRepository $quoteRepository,
        Helper\SendEmail $emailHelper
    ) {
        $this->winnerCollection = $winnerCollection;
        $this->customerSession = $customerSession;
        $this->configHelper = $configHelper;
        $this->date = $datetime;
        $this->cart = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->emailHelper = $emailHelper;
    }

    /**
     * deleting if expired items present in cart
     *
     * @param  Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {

        $winnerData = $this->winnerCollection->create()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
            ->addFieldToFilter('status', 'not purchased');

        try {
            $cartitems = $this->quoteRepository->getActiveForCustomer($this->customerSession->getCustomerId());
        } catch (\Exception $e) {
            return false;
        }

        $enableMail = json_decode((string)$this->configHelper->getConfigData('auction_entry_1/standard/email_product_delete'), true);

        $productIds = [];

        foreach ($cartitems->getAllItems() as $items) {
            $productId = $items->getProductId();
            $itemId = $items->getId();
            array_push($productIds, ['product_id' => $productId, 'item_id' => $itemId]);
        }

        foreach ($winnerData as $winner) {
            $addtocart = $winner->getAddToCart();
            if ($addtocart == true) {
                $bidDate = strtotime($winner->getBidDate());
                $adminTime = $this->configHelper->getConfigData('auction_entry_1/standard/remove_from_cart');
                $days = '+' . $adminTime . 'day';
                $now = $this->date->gmtDate();

                $expiredTime = date('Y-m-d', strtotime($days, $bidDate));
                if ($adminTime && count($productIds) != 0) {
                    if ($expiredTime >= $now) {
                        foreach ($productIds as $id) {
                            if ($id['product_id'] == $winner->getProductId()) {

                                $this->cart->getCart()->removeItem($id['item_id'])->save();
                                $winner->setData('status', 'purchase link expired');
                                $winner->save();

                                /*if ($enableMail) {
                                    $this->emailHelper->sendProductDeleteMail(
                                        $this->customerSession->getCustomerId(),
                                        $id['product_id']);
                                }*/
                            }
                        }
                    }
                }
            }
        }
    }

}
