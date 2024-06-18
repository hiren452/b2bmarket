<?php

namespace Matrix\CsAuction\Preference\AddAuction\Edit\Tab;

use Ced\Auction\Model\Auction;
use Ced\CsAuction\Block\AddAuction\Edit\Tab\AuctionList;
use Ced\CsMembership\Helper\Data;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CustomizedAuctionList extends AuctionList
{
    protected $membershipHelper;

    public function __construct(
        TimezoneInterface $timezone,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ProductRepository $product,
        Auction $auction,
        Data $membershipHelper,
        array $data = []
    ) {
        parent::__construct(
            $timezone,
            $context,
            $registry,
            $formFactory,
            $product,
            $auction,
            $data
        );
        $this->membershipHelper = $membershipHelper;
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Auction Information')]);
        /**
         * save auction
         */
        if ($this->getRequest()->getParam('product_id')) {
            $product = $this->product->getById($this->getRequest()->getParam('product_id'));
            $fieldset->addField(
                'price',
                'text',
                [
                    'name' => 'Price',
                    'label' => __('Original Product Price'),
                    'title' => __('Original Product Price'),
                    'required' => true,
                    'class' => 'validate-number',
                    'value' => $product->getPrice(),
                    'readonly' => true
                ]
            );
            $fieldset->addField(
                'product_id',
                'hidden',
                [
                    'name' => 'Product Name',
                    'label' => __('Product Name'),
                    'title' => __('Product Name'),
                    'class' => '',
                ]
            );
            $fieldset->addField(
                'product_name',
                'text',
                [
                    'name' => 'Product Name',
                    'label' => __('Product Name'),
                    'title' => __('Product Name'),
                    'required' => true,
                    'class' => '',
                    'value' => $product->getName(),
                    'readonly' => true
                ]
            );
            $fieldset->addField(
                'starting_price',
                'text',
                [
                    'name' => 'Starting Price',
                    'label' => __('Starting Price'),
                    'title' => __('Starting Price'),
                    'required' => true,
                    'class' => 'validate-number',
                ]
            );
            $fieldset->addField(
                'max_price',
                'text',
                [
                    'name' => 'Max Price',
                    'label' => __('Max Price'),
                    'title' => __('Max Price'),
                    'class' => 'validate-number',
                ]
            );
            $fieldset->addField(
                'reserve_price',
                'text',
                [
                    'name' => 'reserve_price',
                    'label' => __('Reserve Price'),
                    'title' => __('Reserve Price'),
                    'class' => 'validate-number',
                ]
            );
            $fieldset->addField(
                'start_datetime',
                'date',
                [
                    'name' => 'start_datetime',
                    'label' => __('Start Datetime'),
                    'title' => __('Start Datetime'),
                    'date_format' => 'yyyy-MM-dd',
                    'time_format' => 'H:mm:ss Z',
                    'required' => true,
                    'class' => 'start_time',
                    'value' => $this->timezone->date()
                        ->format('Y-m-d H:i:s Z'),
                ]
            );
            $fieldset->addField(
                'end_datetime',
                'date',
                [
                    'name' => 'end_datetime',
                    'label' => __('End Datetime'),
                    'title' => __('End Datetime'),
                    'date_format' => 'yyyy-MM-dd',
                    'time_format' => 'H:mm:ss Z',
                    'required' => true,
                    'class' => 'end_time',
                    'value' => $this->timezone->date()
                        ->format('Y-m-d H:i:s Z'),
                ]
            );
            $fieldset->addField(
                'sellproduct',
                'select',
                [
                    'name' => 'Sell Product',
                    'label' => __('Sell Product'),
                    'title' => __('Sell Product'),
                    'values' => ['no' => 'No', 'yes' => 'Yes'],
                    'required' => true,
                    'class' => '',
                ]
            );
            $fieldset->addField(
                'auction_type',
                'checkboxes',
                [
                    'name' => _('auction_type'),
                    'label' => __('Auction Type'),
                    'values' => $this->membershipHelper->getAuctionTypes(),
                    'required' => true,
                    'class' => 'validate-one-checkbox-required-by-name',
                    'after_element_html' => '<script type="text/javascript">
                                            require(["jquery"], function($){
                                                $("#auction_type_0").attr("disabled","disabled");
                                                $("#auction_type_0").click(function () {
                                                    if($(this).is(":checked")){
                                                        $("#auction_type_1").attr("checked", false);
                                                    }
                                                });
                                                $("#auction_type_1").click(function () {
                                                    if($(this).is(":checked")){
                                                        $("#auction_type_0").attr("checked", false);
                                                    }
                                                });
                                                $("#auction_type").attr("required", "true");
                                            });
                                      </script>'
                ]
            );
            $fieldset->addField(
                'is_buy_now',
                'select',
                [
                    'name' => 'is_buy_now',
                    'label' => __('Activate Buy Now'),
                    'title' => __('Activate Buy Now'),
                    'values' => ['no' => 'No', 'yes' => 'Yes'],
                    'required' => false,
                    'class' => '',
                    'after_element_html' => '<div style="margin-top:20px;"><input type="hidden" name="go_to_invite" id="go_to_invite" value="0" /><button id="invite_btn" title="Invite Buyer" type="button" class="primary"><span class="ui-button-text">
    <span>Invite Buyer</span>
</span></button></div><script type="text/javascript">
                                            require(["jquery"], function($){
                                                $("#invite_btn").click(function () {
                                                    $("#go_to_invite").val(1);
                                                    $("#save").trigger("click");
                                                });
                                            });
                                      </script>'
                ]
            );
            $this->setForm($form);
            return $this;
        }
        /**
         * edit auction
         */
        if ($this->getRequest()->getParam('id')) {
            $auction = $this->auction->load($this->getRequest()->getParam('id'))->getData();
            $auction['price'] = $this->product->getById($auction['product_id'])->getPrice();
            $auction['product_name'] = $this->product->getById($auction['product_id'])->getName();
            $price = $this->product->getById($auction['product_id'])->getPrice();
            $fieldset->addField(
                'product_name',
                'text',
                [
                    'name' => 'Product Name',
                    'label' => __('Product Name'),
                    'title' => __('Product Name'),
                    'required' => true,
                    'class' => '',
                    'readonly' => true
                ]
            );
            $fieldset->addField(
                'product_id',
                'hidden',
                [
                    'name' => 'Product Name',
                    'label' => __('Product Name'),
                    'title' => __('Product Name'),
                    'required' => true,
                    'class' => '',
                ]
            );
            $fieldset->addField(
                'price',
                'text',
                [
                    'name' => 'Price',
                    'label' => __('Original Product Price'),
                    'title' => __('Original Product Price'),
                    'required' => true,
                    'class' => 'validate-number',
                    'readonly' => true,
                ]
            );
            $fieldset->addField(
                'starting_price',
                'text',
                [
                    'name' => 'Starting Price',
                    'label' => __('Starting Price'),
                    'title' => __('Starting Price'),
                    'required' => true,
                    'class' => 'validate-number',
                    'readonly' => true,
                ]
            );
            $fieldset->addField(
                'max_price',
                'text',
                [
                    'name' => 'Max Price',
                    'label' => __('Max Price?'),
                    'title' => __('Max Price?'),
                    'class' => 'validate-number',
                    'readonly' => true,
                ]
            );
            $fieldset->addField(
                'reserve_price',
                'text',
                [
                    'name' => 'reserve_price',
                    'label' => __('Reserve Price'),
                    'title' => __('Reserve Price'),
                    'class' => 'validate-number',
                    'readonly' => true,
                ]
            );
            $fieldset->addField(
                'start_datetime',
                'date',
                [
                    'name' => 'Start Datetime',
                    'label' => __('Start Datetime'),
                    'title' => __('Start Datetime'),
                    'date_format' => 'yyyy-MM-dd',
                    'time_format' => 'H:mm:ss',
                    'required' => true,
                    'class' => '',
                    'disabled' => true,
                ]
            );
            $fieldset->addField(
                'end_datetime',
                'date',
                [
                    'name' => 'End Datetime',
                    'label' => __('End Datetime'),
                    'title' => __('End Datetime'),
                    'date_format' => 'yyyy-MM-dd',
                    'time_format' => 'H:mm:ss',
                    'required' => true,
                    'class' => '',
                    'disabled' => true,
                ]
            );
            $fieldset->addField(
                'sellproduct',
                'select',
                [
                    'name' => 'Sell Product',
                    'label' => __('Sell Product'),
                    'title' => __('Sell Product'),
                    'values' => ['no' => 'No', 'yes' => 'Yes'],
                    'required' => true,
                    'class' => '',
                ]
            );
            $fieldset->addField(
                'auction_type',
                'checkboxes',
                [
                    'name' => _('auction_type'),
                    'label' => __('Auction Type'),
                    'values' => $this->membershipHelper->getAuctionTypes(),
                    'checked' => [$auction['auction_type']],
                    'required' => true,
                    'class' => 'validate-one-checkbox-required-by-name',
                    'after_element_html' => '<script type="text/javascript">
                                            require(["jquery"], function($){
                                                $("#auction_type_0").click(function () {
                                                    if($(this).is(":checked")){
                                                        $("#auction_type_1").attr("checked", false);
                                                    }
                                                });
                                                $("#auction_type_1").click(function () {
                                                    if($(this).is(":checked")){
                                                        $("#auction_type_0").attr("checked", false);
                                                    }
                                                });
                                            });
                                      </script>'
                ]
            );
            $url = $this->getUrl('csauction/auctionlist/privateauction', ['id' => $this->getRequest()->getParam('id')]);
            $fieldset->addField(
                'is_buy_now',
                'select',
                [
                    'name' => 'is_buy_now',
                    'label' => __('Activate Buy Now'),
                    'title' => __('Activate Buy Now'),
                    'values' => ['no' => 'No', 'yes' => 'Yes'],
                    'required' => false,
                    'class' => '',
                    'after_element_html' => '<div style="margin-top:20px;"><input type="hidden" name="go_to_invite" id="go_to_invite" value="0" /><button id="invite_btn" title="Invite Buyer" type="button" class="primary"><span class="ui-button-text">
    <span>Invite Buyer</span>
</span></button></div><script type="text/javascript">
                                            require(["jquery"], function($){
                                                $("#invite_btn").click(function () {
                                                    $("#go_to_invite").val(1);
                                                    $("#save").trigger("click");
                                                });
                                            });
                                      </script>'
                ]
            );

            $form->setValues($auction);
            $this->setForm($form);
            return $this;
        }
    }

}
