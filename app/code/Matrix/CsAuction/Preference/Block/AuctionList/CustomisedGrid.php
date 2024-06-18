<?php

namespace Matrix\CsAuction\Preference\Block\AuctionList;

class CustomisedGrid extends \Ced\CsAuction\Block\AuctionList\Grid
{
    protected function _prepareColumns()
    {
        //$this->addColumn('product_id', ['header' => __('Product Id'), 'index' => 'product_id','type' => 'number']);

        $this->addColumn('product_name', ['header' => __('Name'), 'index' => 'product_name']);

        $this->addColumn(
            'starting_price',
            [
                'header' => __('Start Price'),
                'type'  => 'currency',
                'index' => 'starting_price',
            ]
        );

        $this->addColumn(
            'max_price',
            [
                'header'=> __('Max Price'),
                'type'  => 'currency',
                'index' => 'max_price',
            ]
        );

        $this->addColumn(
            'reserve_price',
            [
                'header'=> __('Reserve Price'),
                'type'  => 'currency',
                'index' => 'reserve_price',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
            ]
        );

        $this->addColumn(
            'start_datetime',
            [
                'header' => __('Start Datetime'),
                'index' => 'start_datetime',
            ]
        );

        $this->addColumn(
            'end_datetime',
            [
                'header' => __('End Datetime'),
                'index' => 'end_datetime',
            ]
        );
        $this->addColumn(
            'invites',
            [
                'header' => __('Invited Buyer Emails'),
                'caption' => __('Invited Buyer Emails'),
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Renderer\InvitedBuyer',
                'sortable' => false,
                'filter' => false
            ]
        );
        $this->addColumn(
            'sellproduct',
            [
                'header' => __('Sell Product'),
                'index' => 'sellproduct',
            ]
        );

        $this->addColumn(
            'auction_type',
            [
                'header' => __('Auction Type'),
                'index' => 'auction_type',
                'renderer' => 'Matrix\CsAuction\Block\AddAuction\Renderer\PrivateEdit',
            ]
        );

        $this->addColumn(
            'is_paid',
            [
                'header' => __('Auction Fee'),
                'index' => 'is_paid',
                'renderer' => 'Matrix\CsAuction\Block\AddAuction\Renderer\AuctionFee',
            ]
        );

        $this->addColumn(
            'edits',
            [
                'header' => __('Edit'),
                'caption' => __('Edit'),
                'renderer' => 'Ced\CsAuction\Block\AddAuction\Renderer\Edit',
                'sortable' => false,
                'filter' => false
            ]
        );

        //return parent::_prepareColumns();
    }
}
