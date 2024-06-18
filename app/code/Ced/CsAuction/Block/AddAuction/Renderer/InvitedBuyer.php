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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\AddAuction\Renderer;

use Matrix\CsAuction\Model\ResourceModel\PrivateAuction\CollectionFactory as PrivateCollection;
use OX\Auction\Helper\Data as HelperData;

/**
 * Class InvitedBuyer
 *
 * @package Ced\CsAuction\Block\AddAuction\Renderer
 */
class InvitedBuyer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param  \Magento\Framework\DataObject $row
     * @return string
     */

    /**
     * @var PrivateCollection
     */
    protected $privateBuyer;

    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * AuctionStart constructor.
     *
     * @param PrivateCollection $privateBuyer
     * @param HelperData        $helperData
     */
    public function __construct(
        PrivateCollection $privateBuyer,
        HelperData $helperData
    ) {
        $this->privateBuyer = $privateBuyer;
        $this->helperData = $helperData;
    }
    public function render(\Magento\Framework\DataObject $row)
    {

        $rowId = $row->getId();
        $privateBuyer = $this->privateBuyer->create()
            ->addFieldToFilter('auction_id', $rowId)->getFirstItem();
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
            $to = array_map("unserialize", array_unique(array_map("serialize", $to)));
            $array = array_column($to, 'email');
            $allEmail = implode(", ", $array);
            return $allEmail;

        }
        return "No-one Invited";
    }
}
