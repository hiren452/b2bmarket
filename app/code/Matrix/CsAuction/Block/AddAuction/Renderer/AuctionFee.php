<?php

namespace Matrix\CsAuction\Block\AddAuction\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class AuctionFee extends AbstractRenderer
{

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $return = '';
        if($row->getIsPaid() == 1) {
            $return = __('Paid');
        } else {
            $return = __('Not Paid');

            $url = $this->getUrl('csauction/auctionlist/pay', ['id' => $row->getId()]);
            $url = '<br /><a href="' . $url . '" target="_self">' . __('Pay') . '</a>';

            if($row->getStatus() == 'processing') {
                $return .= '' . $url;
            }
        }

        return $return;
    }
}
