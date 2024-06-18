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

namespace Ced\CsAuction\Block\AddAuction\Edit\Tab;

use Ced\Auction\Model\Auction;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class AuctionList extends \Magento\Backend\Block\Widget\Form\Generic
{
    public function __construct(
        TimezoneInterface $timezone,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        ProductRepository $product,
        Auction $auction,
        array $data = []
    ) {

        parent::__construct($context, $registry, $formFactory, $data);
        $this->product = $product;
        $this->auction = $auction;
        $this->timezone = $timezone;
        $this->setData('area', 'adminhtml');
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
                    'title' => __('Starting Price'),
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
                    'time_format' => 'H:mm:ss',
                    'required' => true,
                    'class' => '',
                    'value' => $this->timezone->date()
                        ->format('Y-m-d H:i:s'),
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
                    'time_format' => 'H:mm:ss',
                    'required' => true,
                    'class' => '',
                    'value' => $this->timezone->date()
                        ->format('Y-m-d H:i:s'),
                ]
            );

            $fieldset->addField(
                'sellproduct',
                'select',
                [
                    'name' => 'Sell Product',
                    'label' => __('Sell Product'),
                    'title' => __('Sell Product'),
                    'values'=> ['no'=>'No','yes'=>'Yes'],
                    'required' => true,
                    'class' => '',
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
                    'label' => __('Max Price'),
                    'title' => __('Starting Price'),
                    'class' => 'validate-number',
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
                    'readonly' => true,
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
                ]
            );

            $fieldset->addField(
                'sellproduct',
                'select',
                [
                    'name' => 'Sell Product',
                    'label' => __('Sell Product'),
                    'title' => __('Sell Product'),
                    'values'=> ['no'=>'No','yes'=>'Yes'],
                    'required' => true,
                    'class' => '',
                ]
            );

            $form->setValues($auction);
            $this->setForm($form);
            return $this;
        }
    }

}
