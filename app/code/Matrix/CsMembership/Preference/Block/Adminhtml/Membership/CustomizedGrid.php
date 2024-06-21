<?php

namespace Matrix\CsMembership\Preference\Block\Adminhtml\Membership;

class CustomizedGrid extends \Ced\CsMembership\Block\Adminhtml\Membership\Grid
{

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_membershipFactory->create()->getCollection();
        $collection->addFieldToFilter('name', ['neq' => '']);
        $filter=$this->getRequest()->getParams();
        if (isset($filter['id']) && $filter['id']!='') {
            $collection->addFieldToFilter('id', $this->getRequest()->getParam('id'));
        }

        $this->setCollection($collection);
        return  parent::_prepareCollection();
    }
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
                'header'    => __('ID'),
                'align'     =>'right',
                'index'     => 'id',
                "width" => "50px",
                'type'    => 'number'
            ]);

        $this->addColumn("name", [
                "header" => __("Title"),
                "index" => "name",
            ]);

        $this->addColumn("duration", [
                "header" => __("Duration (In month(s))"),
                "index" => "duration",
            ]);
        $this->addColumn("product_limit", [
                "header" => __("No of Product"),
                "index" => "product_limit",
            ]);

        $this->addColumn("auction_limit", [
                "header" => __("Auction Limit"),
                "index" => "auction_limit",
            ]);

        $store = $this->_getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Package Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'special_price',
            [
                'header' => __('Package Special Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'special_price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => [1 => 'Yes', 0 => 'No']
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
    }
}
