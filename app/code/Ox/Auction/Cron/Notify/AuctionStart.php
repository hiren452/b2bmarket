<?php

namespace OX\Auction\Cron\Notify;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory as PrivateCollection;
use OX\Auction\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

class AuctionStart
{
    /** @var LoggerInterface */
    protected $logger;
    /** @var CollectionFactory */
    protected $collectionFactory;
    /** @var TimezoneInterface */
    protected $timezoneInterface;
    /** @var HelperData */
    protected $helperData;
    /** @var PrivateCollection */
    protected $privateBuyer;

    public $customerFactory;

    protected $resourceBidDetails;

    /**
     * AuctionStart constructor.
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param TimezoneInterface $timezoneInterface
     * @param HelperData $helperData
     * @param PrivateCollection $privateBuyer
     */
    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezoneInterface,
        HelperData $helperData,
        PrivateCollection $privateBuyer,
        \Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory $resourceBidDetails,
        CustomerFactory $customerFactory
    ) {
        $this->privateBuyer = $privateBuyer;
        $this->helperData = $helperData;
        $this->timezone = $timezoneInterface;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->resourceBidDetails = $resourceBidDetails;
        $this->customer = $customerFactory;
    }

    /**
     * Check every 5 mins to send email for auction start
     *
     * @return void
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/cron-commands.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Logger For Indexer Start");

        $currentTime = $this->timezone->date()->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $incrementedTime = $this->timezone->date()->setTimezone(new \DateTimeZone('UTC'))->modify('+15 minutes')->format('Y-m-d H:i:s');

        $logger->info($currentTime, true);
        $logger->info($incrementedTime, true);

        $auctionCollection = $this->collectionFactory->create()->addFieldToFilter('start_time_utc', ['from' => $currentTime, 'to' => $incrementedTime]);
        $logger->info($auctionCollection->getSelect(), true);
        $logger->info($auctionCollection->count(), true);

        if ($auctionCollection->count()) {
            foreach ($auctionCollection as $auction) {
                $vendor = $this->helperData->getVendor($auction->getVendorId());
                $toDetails = [['name' => $vendor->getName(), 'email' => $vendor->getEmail()],
                    ['name' => 'Admin', 'email' => $this->helperData->getAdminEmailId()]];
                $templateVars = [];
                $templateVars['subject'] = 'Reminder For Auction';
                $templateVars['title'] = 'Auction to be started for the following product';
                $templateVars['product_name'] = $auction->getProductName();
                $templateVars['product_url'] = $this->helperData->getProductDetail($auction->getProductId())
                    ->getProductUrl();
                $templateVars['auction_status'] = $auction->getStatus();
                $templateVars['starting_date'] = $this->timezone->date($auction->getData('start_time_utc'))->format('Y-m-d H:i');
                $templateVars['ending_date'] = $this->timezone->date($auction->getData('end_time_utc'))->format('Y-m-d H:i');
                $toDetails = $this->getToRecipients($auction, $toDetails);
                $emailTemplate = 'auction_entry_1_standard_auction_notify';
                $logger->info("Reached to the conditions");
                foreach ($toDetails as $toDetail) {
                    $templateVars['recipient_name'] = $toDetail['name'];
                    $this->helperData->sendEmail($templateVars, $emailTemplate, $toDetail['email']);
                    $logger->info("Email Sent");
                }
            }
        }

        $endauctionCollection = $this->collectionFactory->create()->addFieldToFilter('end_time_utc', ['from' => $currentTime, 'to' => $incrementedTime]);
        $endauctionCollection->addFieldToFilter('status', 'processing');
        if ($endauctionCollection->count()) {
            foreach ($endauctionCollection as $auction) {
                $vendor = $this->helperData->getVendor($auction->getVendorId());
                $toDetails = [['name' => $vendor->getName(), 'email' => $vendor->getEmail()],
                ['name' => 'Admin', 'email' => $this->helperData->getAdminEmailId()]];
                $templateVars = [];
                $templateVars['subject'] = 'Reminder For Auction Closing';
                $templateVars['title'] = 'Auction is going to be closed for the following product';
                $templateVars['product_name'] = $auction->getProductName();
                $templateVars['product_url'] = $this->helperData->getProductDetail($auction->getProductId())
                    ->getProductUrl();
                $templateVars['auction_status'] = 'going To Close Soon';
                $templateVars['starting_date'] = $this->timezone->date($auction->getData('start_time_utc'))->format('Y-m-d H:i');
                $templateVars['ending_date'] = $this->timezone->date($auction->getData('end_time_utc'))->format('Y-m-d H:i');
                $toDetails = $this->getToRecipients($auction, $toDetails);

                $biddetails = $this->resourceBidDetails->create()
                ->addFieldToFilter('product_id', $auction->getProductId())
                ->addFieldToFilter('auction_id', $auction->getId())
                ->addFieldToFilter('status', 'bidding');
                foreach($biddetails as $biddetail) {
                    $customerId = $biddetail['customer_id'];
                    if($customerId) {
                        $customer = $this->customer->create()->load($customerId);
                    }
                    if($customer) {
                        $customerDetail['name'] = $customer->getName();
                        $customerDetail['email'] = $customer->getEmail();
                        array_push($toDetails, $customerDetail);
                    }

                }
                $emailTemplate = 'auction_entry_1_standard_auction_closed';
                $uniqueContacts = array_reduce($toDetails, function ($result, $contact) {
                    $email = $contact['email'];
                    if (!isset($result[$email])) {
                        $result[$email] = $contact;
                    }
                    return $result;
                }, []);
                $uniqueContacts = array_values($uniqueContacts);
                foreach ($toDetails as $toDetail) {
                    $templateVars['recipient_name'] = $toDetail['name'];
                    $this->helperData->sendEmail($templateVars, $emailTemplate, $toDetail['email']);
                }
            }
        }
    }

    /**
     * Get To Recipients
     *
     * @param mixed $auction
     * @param array $to
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getToRecipients($auction, $to = [])
    {

        if ($auction->getAuctionType() != 0) {
            $privateBuyer = $this->privateBuyer->create()
                ->addFieldToFilter('auction_id', $auction->getId())->getFirstItem();
            $customerIds = ($privateBuyer->getCustomerIds() != null && $privateBuyer != '') ?
                explode(",", $privateBuyer->getCustomerIds()) : [];
            foreach ($customerIds as $customerId) {
                try {
                    $customer = $this->helperData->getCustomerDetails($customerId);
                    $customerDetail['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                    $customerDetail['email'] = $customer->getEmail();
                    array_push($to, $customerDetail);
                } catch (Exception $e) {
                    $this->logger->error($e->getMessage());
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
}
