<?php

namespace Matrix\CsMembership\Preference\Block\Adminhtml\Membership\Edit\Tab;

use Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab\Form;
use Ced\CsMembership\Model\System\Config\Source\Duration;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;

class CustomizedForm extends Form
{
    /**
     * @var Config
     */
    private $wysiwygConfig;
    /**
     * @var Yesno
     */
    private $_yesNo;
    /**
     * @var Duration
     */
    private $durationSource;

    public function __construct(
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $yesNo,
        Duration $durationSource,
        array $data = []
    ) {
        parent::__construct(
            $backendHelper,
            $collectionFactory,
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->wysiwygConfig = $wysiwygConfig;
        $this->_yesNo = $yesNo;
        $this->durationSource = $durationSource;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('csmembership_member_data');
        $isElementDisabled = false;

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Membership Plan Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $yesno = $this->_yesNo->toOptionArray();

        $script = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Membership Name'),
                'title' => __('Membership Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Membership Price'),
                'title' => __('Membership Price'),
                'required' => true,
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'product_limit',
            'text',
            [
                'name' => 'product_limit',
                'label' => __('# of catalogue products'),
                'title' => __('# of catalogue products'),
                'required' => true,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );
        $fieldset->addField(
            'auction_limit',
            'text',
            [
                'name' => 'auction_limit',
                'label' => __('# of auctions'),
                'title' => __('# of auctions'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );
        $fieldset->addField(
            'private_auction',
            'select',
            [
                'name' => 'private_auction',
                'label' => __('Private Auction'),
                'title' => __('Private Auction'),
                'required' => true,
                'values' => $yesno,
                'class' => 'required-entry validate-select'
            ]
        );
        $fieldset->addField(
            'public_auction',
            'select',
            [
                'name' => 'public_auction',
                'label' => __('Public Auction'),
                'title' => __('Public Auction'),
                'required' => true,
                'values' => $yesno,
                'class' => 'required-entry validate-select'
            ]
        );
        $fieldset->addField(
            'auction_fee',
            'text',
            [
                'name' => 'auction_fee',
                'label' => __('Auction Fee'),
                'title' => __('Auction Fee'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('No. of Subscription Allowed'),
                'title' => __('No. of Subscription Allowed'),
                'required' => true,
                'class' => 'validate-number'
            ]
        );

        if ($this->getRequest()->getParam('id')) {
            $fieldset->addField(
                'duration',
                'select',
                [
                    'name' => 'duration',
                    'label' => __('Duration'),
                    'title' => __('Duration'),
                    'required' => true,
                    'class' => 'required-entry validate-select',
                    'values' => $this->durationSource->durationArray($this->_coreRegistry->registry("csmembership_data")->getGroupType()),
                    'after_element_html' => '<br><small>If option is blank then set duration price in Config<small>'
                ]
            );
        } else {
            $fieldset->addField(
                'duration',
                'select',
                [
                    'name' => 'duration',
                    'label' => __('Duration'),
                    'title' => __('Duration'),
                    'required' => true,
                    'class' => 'required-entry validate-select',
                    'values' => $this->durationSource->durationArray($this->getRequest()->getParam('group_type')),
                    'after_element_html' => '<br><small>If option is blank then set duration price in Config<small>'
                ]
            );
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => $yesno,
                'class' => 'required-entry validate-select'
            ]
        );
        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'website_id',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => true,
                'values' => Form::getWebSites(),
                'class' => 'required-entry validate-select'
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height:10em',
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );
        $fieldset->addField(
            'special_price',
            'text',
            [
                'name' => 'special_price',
                'label' => __('Special Price'),
                'title' => __('Special Price'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'class' => 'required required-entry required-file'
            ]
        );
        $fieldset->addField(
            'product_id',
            'hidden',
            [
                'name' => 'product_id'
            ]
        );
        $cat = $fieldset->addField(
            'category_ids',
            'hidden',
            [
                'name' => 'category_ids'
            ]
        );

        $script->setAfterElementHtml("<script type=\"text/javascript\">
            function getTotal(){
                var product_limit=parseInt(document.getElementById('page_product_limit').value);
                var category_ids=document.getElementById('product_categories').value;
                var price=document.getElementById('page_price').value;
                var special_price=document.getElementById('page_special_price').value;
                var duration=parseInt(document.getElementById('page_duration').value);
                var reloadurl = '" . $this->backendHelper->getUrl('csmembership/membership/getTotal', ['_current' => true]) . "';
                if(product_limit){
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        parameters: {product_limit:product_limit,category_ids:category_ids,price:price,special_price:special_price,duration:duration },
                        onComplete: function(stateform) {
                            var response=stateform.responseText;
                            var result=isNumber(response);
                            if(result)
                                document.getElementById('page_price').value=response;
                        }
                    });
                }
            }
            function isNumber(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            }
            </script>");

        $cat->setAfterElementHtml($this->getLayout()->createBlock("Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab\Categories")->toHtml());

        $cat->setAfterElementHtml($this->getLayout()->createBlock("Matrix\CsMembership\Block\Adminhtml\Membership\Edit\Tab\Commission")->toHtml());

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        if ($this->_coreRegistry->registry("csmembership_group_data")) {
            $form->setValues($this->_coreRegistry->registry("csmembership_group_data"));
        } else {
            $form->setValues($this->_coreRegistry->registry("csmembership_data")->getData());
        }
        $this->setForm($form);
    }
}
