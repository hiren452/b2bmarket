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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Block\Adminhtml\Rewrites\CsVattribute\Edit\Tab;

use Ced\CsVendorAttribute\Block\Adminhtml\Attributes\Edit\Tab\Main as VattributeMain;

class Main extends VattributeMain
{

    /**
     * Main constructor.
     * @param \Ced\CsMultistepreg\Model\ResourceModel\Steps\CollectionFactory $stepsCollection
     */
    public function __construct(
        \Ced\CsMultistepreg\Model\Steps $stepsCollection,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->stepsCollection = $stepsCollection;
        parent::__construct($context, $registry, $formFactory, $eavData, $yesnoFactory, $inputTypeFactory, $propertyLocker, $yesno, $data);
    }

    public function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();

        $fieldset = $form->getElement('base_fieldset');
        $fieldsetMultistep = $form->addFieldset('registration_step_no_legend', ['legend' => __('Vendor Multistep Registration Form')]);

        $stepValues = $this->toOptionArray();
        $fieldsetMultistep->addField(
            'registration_step_no',
            'select',
            [
                'name' => 'registration_step_no',
                'label' => __('Step Number'),
                'title' => __('Step Number'),
                'values' => $stepValues,
                'note' => __('Step Number In Multistep Registration Form.'),
            ]
        );
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $steps = ['' => __('Please Select')];
        $stepCollection = $this->stepsCollection->getCollection();
        foreach ($stepCollection as $step) {
            $temp = [];
            $stepNumber = $step->getStepNumber();
            $stepLabel = $step->getStepLabel();
            $temp['value'] = $stepNumber;
            $temp['label'] = $stepLabel;
            $steps[] = $temp;
        }

        return $steps;
    }
}
