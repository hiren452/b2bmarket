<?php

namespace OX\Auction\Cron\Notify;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory as PrivateCollection;
use OX\Auction\Helper\Data as HelperData;
use OX\Auction\Model\ResourceModel\NotifyDates\CollectionFactory as NotifyDatesCollection;
use Psr\Log\LoggerInterface;

class AuctionReminder
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var CollectionFactory */
    protected $collectionFactory;
    /** @var TimezoneInterface */
    protected $timezone;
    /** @var HelperData */
    protected $helperData;
    /** @var PrivateCollection */
    protected $privateBuyer;
    /** @var NotifyDatesCollection */
    protected $notifyDateCollection;

    /**
     * AuctionReminder constructor.
     *
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $timezoneInterface
     * @param HelperData $helperData
     * @param PrivateCollection $privateBuyer
     * @param NotifyDatesCollection $notifyDateCollection
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezoneInterface,
        HelperData $helperData,
        PrivateCollection $privateBuyer,
        NotifyDatesCollection $notifyDateCollection
    ) {
        $this->notifyDateCollection = $notifyDateCollection;
        $this->privateBuyer = $privateBuyer;
        $this->helperData = $helperData;
        $this->timezone = $timezoneInterface;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Check every midnight to notify about auction
     *
     * @return void
     */
    public function execute()
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron-commands.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Logger For Indexer Start");
        $this->sendEmailForUpcomingAuction();
        $this->sendEmailForOngoingAuction();
    }

    /**
     * Send Email to Upcoming auction
     */
    public function sendEmailForUpcomingAuction()
    {

        $currentDate = $this->timezone->date()->setTimezone(new \DateTimeZone('GMT'))->format('Y-m-d');
        $auctionIdCollection = $this->notifyDateCollection->create()
            ->addFieldToFilter('notify_date', $currentDate);
        $auctionIds = [];
        foreach ($auctionIdCollection as $auction) {
            array_push($auctionIds, $auction->getAuctionId());
            $auction->delete();
        }
        if (!empty($auctionIds)) {
            $auctionCollection = $this->collectionFactory->create()
                ->addFieldToFilter('id', ['in', $auctionIds]);
            $subject = 'Reminder For upcoming Auction';
            $title = 'Please check the following details for upcoming auction';
            $this->buildTemplateParamsAndSend($auctionCollection, $subject, $title);
        }
    }

    /**
     * Build Template Params and send email
     *
     * @param mixed $auctionCollection
     * @param string $subject
     * @param string $title
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function buildTemplateParamsAndSend($auctionCollection, $subject, $title)
    {
        if ($auctionCollection->count()) {
            foreach ($auctionCollection as $auction) {

                $toDetails = [];
                $templateVars = [];
                $templateVars['subject'] = $subject;
                $templateVars['title'] = $title;
                $templateVars['product_name'] = $auction->getProductName();
                $templateVars['product_url'] = $this->helperData->getProductDetail($auction->getProductId())
                    ->getProductUrl();
                $templateVars['auction_status'] = $auction->getStatus();
                $templateVars['starting_date'] = $this->timezone->date($auction->getData('start_time_utc'))->format('Y-m-d H:i');
                $templateVars['ending_date'] = $this->timezone->date($auction->getData('end_time_utc'))->format('Y-m-d H:i');
                $toDetails = $this->getToRecipients($auction);
                if (!empty($toDetails)) {
                    $emailTemplate = 'auction_entry_1_standard_auction_notify';
                    foreach ($toDetails as $toDetail) {
                        $templateVars['recipient_name'] = $toDetail['name'];
                        $this->helperData->sendEmail($templateVars, $emailTemplate, $toDetail['email']);
                    }
                }
            }
        }
    }

    /**
     * Get To Recipients
     *
     * @param mixed $auction
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getToRecipients($auction)
    {
        $to = [];
        if ($auction->getAuctionType() != 0) {

            $privateBuyer = $this->privateBuyer->create()
                ->addFieldToFilter('auction_id', $auction->getId())->getFirstItem();
            $customerIds = ($privateBuyer->getCustomerIds() != null && $privateBuyer != '') ?
                explode(",", $privateBuyer->getCustomerIds()) : [];
            $vendor = $this->helperData->getVendor($privateBuyer->getVendorId());
            $vendorDetails['name'] = $vendor->getName();
            $vendorDetails['email'] = $vendor->getEmail();
            array_push($to, $vendorDetails);
            foreach ($customerIds as $customerId) {
                try {
                    $customer = $this->helperData->getCustomerDetails($customerId);
                    $customerDetail['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerDetail['email'] = $customer->getEmail();

                    array_push($to, $customerDetail);
                } catch (Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
            if ($privateBuyer->getCustomerEmails() && $privateBuyer->getCustomerEmails() != '') {
                $nonRegBuyers = $this->helperData->serializer->unserialize($privateBuyer->getCustomerEmails());
                foreach ($nonRegBuyers as $nonRegBuyer) {
                    if (isset($nonRegBuyer['email']) && $nonRegBuyer['email'] != '') {
                        $nonBuyerDetail['name'] = $nonRegBuyer['first_name'] . ' ' . $nonRegBuyer['last_name'];
                        $nonBuyerDetail['email'] = $nonRegBuyer['email'];
                        array_push($to, $nonBuyerDetail);
                    }
                }
            }
        }
        return $to;
    }

    /**
     * Send email for ongoing Auctions
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendEmailForOngoingAuction()
    {

        $auctionCollection = $this->collectionFactory->create()
            ->addFieldToFilter('status', 'processing')
            ->addFieldToFilter('status', ['neq' => 'closed'])
            ->addFieldToFilter('status', ['neq' => 'terminated']);
        $subject = 'Reminder For ongoing Auction';
        $title = 'Please check the following ongoing auction';
        $this->buildTemplateParamsAndSend($auctionCollection, $subject, $title);
    }
}
