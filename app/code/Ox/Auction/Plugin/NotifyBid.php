<?php

namespace OX\Auction\Plugin;

use Ced\CsAuction\Controller\StartBid\Start;
use Magento\Customer\Model\CustomerFactory;
use OX\Auction\Helper\Data;

class NotifyBid
{
    /** @var Data */
    protected $helperData;
    /** @var string  toEmailaddress */
    protected $toEmailaddress;

    /**
     * NotifyBid constructor.
     *
     * @param Data $helperData
     */

    protected $customerdata;

    public function __construct(
        Data $helperData,
        customerFactory $customerdata
    ) {
        $this->helperData = $helperData;
        $this->customerdata = $customerdata;
    }

    /**
     * Send Email by condition
     *
     * @param Start $subject
     * @param mixed $result
     * @return array $result
     */
    public function afterGetBidData(Start $subject, $result)
    {
        try {
            if ($this->helperData->isSendEmailenabledForbid()) {

                $templateVars = $this->buildTemplateVars($subject, $result);
                $templateId = ($result['status'] == 'won') ? $this->helperData
                    ->getNotifyWinTempForVendor() : $this->helperData->getTemplateForBid();
                $this->helperData->sendEmail($templateVars, $templateId, $this->toEmailaddress);
                $this->sendEmailtoBuyer($subject, $result);
            }
        } catch (\Exception $e) {
            $this->helperData->logger->critical($e->getMessage());
        }

        return $result;
    }

    /**
     * Build template Vars
     *
     * @param mixed $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildTemplateVars($subject, $result)
    {
        $vendorData = $this->helperData->getVendor($result['vendor_id']);
        $productDetails = $this->helperData->getProductDetail($result['product_id']);
        $templateVars = [];
        if ($result['status'] == 'won') {
            $winnerData = $subject->emailHelper->winnerCollection->create()
                ->addFieldToFilter('product_id', $result['product_id'])
                ->addFieldToFilter('status', 'not purchased')->getLastItem();
            $templateVars['store'] = $subject->emailHelper->storeManager->getStore();
            $templateVars['customer_name'] = $winnerData->getCustomerName();
            $templateVars['winning_price'] = $subject->emailHelper->currencyHelper
                ->currency($winnerData->getWinningPrice(), true, false);
            $templateVars['auction_price'] = $subject->emailHelper
                ->currencyHelper->currency($winnerData->getAuctionPrice(), true, false);
            $templateVars['winning_date'] = $winnerData->getBidDate();
        } else {
            $templateVars['vendor_name'] = $vendorData->getName();
            $templateVars['bid_amount'] = $subject->emailHelper->currencyHelper
                ->currency($result['bid_price'], true, false);
        }
        $templateVars['product_name'] = $productDetails->getName();
        $templateVars['product_url'] = $productDetails->getProductUrl();
        $this->toEmailaddress = $vendorData->getEmail();
        return $templateVars;
    }
    public function sendEmailtoBuyer($subject, $result)
    {

        $customerFactory = $this->customerdata->create()->load($result['customer_id']);
        $customerEmail = $customerFactory->getEmail();
        $productDetails = $this->helperData->getProductDetail($result['product_id']);
        $templateVars['subject'] = 'New Bid';
        $templateVars['title'] = 'You have placed an bid for the below auction product';
        $templateVars['product_url'] = $productDetails->getProductUrl();
        $templateVars['recipient_name'] = $customerFactory->getName();
        $templateVars['product_name'] = $productDetails->getName();
        $templateVars['auction_amount'] = $subject->emailHelper->currencyHelper
        ->currency($result['bid_price'], true, false);
        $emailTemplate = 'auction_entry_1_standard_auction_bid_buyer';
        $this->helperData->sendEmail($templateVars, $emailTemplate, $customerEmail);

    }
}
