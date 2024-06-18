<?php
namespace Ced\CsAuction\Ui\Component\Listing\Column;

use Exception;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Matrix\CsAuction\Model\PrivateAuctionFactory;
use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory;
use OX\Auction\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

class InvitedBuyer extends Column
{
    /**
     * @var CollectionFactory
     */
    protected $privateAuctionCollectionFactory;

    /**
     * @var PrivateAuctionFactory
     */
    protected $privateAuctionFactory;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * InvitedBuyer constructor.
     *
     * @param ContextInterface      $context
     * @param UiComponentFactory    $uiComponentFactory
     * @param CollectionFactory     $privateAuctionCollectionFactory
     * @param PrivateAuctionFactory $privateAuctionFactory
     * @param HelperData            $helperData
     * @param LoggerInterface       $logger
     * @param array                 $components
     * @param array                 $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $privateAuctionCollectionFactory,
        PrivateAuctionFactory $privateAuctionFactory,
        HelperData $helperData,
        LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->privateAuctionCollectionFactory = $privateAuctionCollectionFactory;
        $this->privateAuctionFactory = $privateAuctionFactory;
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            $i = 0;
            foreach ($dataSource['data']['items'] as & $item) {
                $rowId = $item['id'];
                $privateBuyer = $this->privateAuctionCollectionFactory->create()->addFieldToFilter('auction_id', $rowId)->getFirstItem();
                $to = [];
                if(!empty($privateBuyer->getData())) {
                    $customerIds = ($privateBuyer->getCustomerIds() != null && $privateBuyer != '') ?
                    explode(",", $privateBuyer->getCustomerIds()) : [];

                    foreach ($customerIds as $customerId) {
                        try {
                            $customer = $this->helperData->getCustomerDetails($customerId);
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
                                $nonBuyerDetail['email'] = $nonRegBuyer['email'];
                                array_push($to, $nonBuyerDetail);
                            }
                        }
                    }
                    if (!empty($to)) {
                        $to = array_map("unserialize", array_unique(array_map("serialize", $to)));

                        $array = array_column($to, 'email');
                        $allEmail = implode(", ", $array);

                        $dataSource['data']['items'][$i]['customer_emails'] = $allEmail;
                    }
                }
                if (empty($to)) {
                    $dataSource['data']['items'][$i]['customer_emails'] = "No-one Invited";
                }
                $i++;
                $to = [];
            }
            return $dataSource;
        }

    }

}
