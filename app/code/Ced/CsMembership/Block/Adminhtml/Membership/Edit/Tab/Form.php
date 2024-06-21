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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab;

use Ced\CsMembership\Model\System\Config\Source\Duration;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Form extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Duration
     */
    protected $duration;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Duration $duration
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Duration $duration,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        $this->collectionFactory = $collectionFactory;
        $this->duration = $duration;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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

        $script = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'required' => true,
                'onchange' => 'getTotal()',
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'product_limit',
            'text',
            [
                'name' => 'product_limit',
                'label' => __('Product Limit'),
                'title' => __('Product Limit'),
                'onchange' => 'getTotal()',
                'required' => true,
                'class' => 'validate-number validate-zero-or-greater'
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
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('No. of Subscription Allowed'),
                'title' => __('No. of Subscription Allowed'),
                'onchange' => 'getTotal()',
                'required' => true,
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'special_price',
            'text',
            [
                'name' => 'special_price',
                'label' => __('Special Price'),
                'title' => __('Special Price'),
                'onchange' => 'getTotal()',
                'class' => 'validate-number'
            ]
        );

        $groupType = $model ? $model->getGroupType() : $this->getRequest()->getParam('group_type');
        $durationOptions = $this->duration->durationArray($groupType);

        $fieldset->addField(
            'duration',
            'select',
            [
                'name' => 'duration',
                'label' => __('Duration'),
                'title' => __('Duration'),
                'required' => true,
                'values' => $durationOptions,
                'onchange' => 'getTotal()',
                'class' => 'required-entry validate-select',
                'after_element_html' => '<br><small>If option is blank then set duration price in Config<small>'
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => \Ced\CsMembership\Block\Adminhtml\Membership\Grid::getValueArray3(),
                'class' => 'required-entry validate-select',
                'onchange' => 'getTotal()'
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
                'values' => $this->getWebSites(),
                'class' => 'required-entry validate-select',
                'onchange' => 'getTotal()'
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

        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        if ($this->_coreRegistry->registry("csmembership_group_data")) {
            $form->setValues($this->_coreRegistry->registry("csmembership_group_data"));
        } else {
            $form->setValues($this->_coreRegistry->registry("csmembership_data")->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Membership Information');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Membership Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @param $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * getWebsites
     * @return array
     */
    public function getWebSites()
    {
        $websites = [];
        $websiteCollection = $this->collectionFactory->create();
        foreach ($websiteCollection as $website) {
            $websites[] = ['value' => $website->getId(), 'label' => __($website->getName())];
        }
        return $websites;
    }
}
