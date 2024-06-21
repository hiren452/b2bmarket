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

namespace Matrix\CsMembership\Block\Adminhtml\Alacart\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    public $_yesNo;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Form constructor.
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->backendHelper = $backendHelper;
        $this->collectionFactory = $collectionFactory;
        $this->_yesNo = $yesNo;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('csmembership_alacart_data');

        $isElementDisabled = false;

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Ala Cart Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $yesno = $this->_yesNo->toOptionArray();

        $name = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'readonly' => true
            ]
        );

        /*$fieldset->addField(
            'status',
            'select',
            array(
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => $yesno,
                'class' => 'required-entry validate-select',
                //'onchange' => 'getTotal()'
            )
        );*/

        $name->setAfterElementHtml($this->getLayout()->createBlock("Matrix\CsMembership\Block\Adminhtml\Alacart\Edit\Tab\Price")->toHtml());

        /*if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }*/

        $form->setValues($this->_coreRegistry->registry("csmembership_alacart_data")->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Ala Cart Information');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Ala Cart Information');
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
}
