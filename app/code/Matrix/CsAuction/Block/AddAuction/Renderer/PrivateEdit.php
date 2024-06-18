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
 * @category    Ced
 * @package     Ced_CsAuction
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Matrix\CsAuction\Block\AddAuction\Renderer;

/**
 * Class PrivateEdit
 * @package Ced\CsAuction\Block\AddAuction\Renderer
 */
class PrivateEdit extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $return = '';
        if($row->getAuctionType() == 1) {
            $return = __('Private');
        } else {
            $return = __('Public');
        }
        $url = $this->getUrl('csauction/auctionlist/privateauction', ['id' => $row->getId()]);
        $url = '<br /><a href="' . $url . '" target="_self">' . __('Edit Invitation') . '</a>';

        if($row->getStatus() == 'processing') {
            $return .= '' . $url;
        }

        return $return;
    }
}
