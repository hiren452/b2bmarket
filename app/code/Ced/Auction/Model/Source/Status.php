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

namespace Ced\Auction\Model\Source;

/**
 * Class Status
 *
 * @package Ced\Auction\Model\Source
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        /*$statuses = [
           \Ced\Auction\Model\Auction::AUCTION_PROCESSING  => "Processing",
            \Ced\Auction\Model\Auction::AUCTION_COMPLETE => "Completed",
            \Ced\Auction\Model\Auction::AUCTION_CANCELLED => 'Cancelled',
            \Ced\Auction\Model\Auction::AUCTION_CLOSED => 'BidClosed'
        ];*/

        $statuses = [
            'no' => 'No',
            'yes' => 'Yes'
        ];
        $ret = [];

        foreach ($statuses as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }
}
