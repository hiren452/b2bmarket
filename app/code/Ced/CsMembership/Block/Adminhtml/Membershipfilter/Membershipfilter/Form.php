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

namespace Ced\CsMembership\Block\Adminhtml\Membershipfilter\Membershipfilter;

/**
 * Class Form (for creating form)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * Form constructor.
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->membershipHelper = $membershipHelper;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );

        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter By Plans')]);

        $script = $fieldset->addField(
            'member_id',
            'select',
            [
                'name' => 'member_id',
                'onchange' => "addDropDown(this)",
                'options' => $this->membershipHelper->getMemberships(),
                'label' => __('Choose Plan Filter')
            ]
        );

        $script->setAfterElementHtml("<script type=\"text/javascript\">
            function addDropDown(data){
                var membershipId = data.value;
                var reloadurl = '" . $this->getUrl("csmembership/membership/orderbymembership") . "'+'id/' + membershipId;
                 window.location.href=reloadurl;
            }
            </script>");

        $form->setUseContainer(true);
        if ($id = $this->getRequest()->getParam('id')) {
            $form->setValues(['member_id' => $id]);
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
