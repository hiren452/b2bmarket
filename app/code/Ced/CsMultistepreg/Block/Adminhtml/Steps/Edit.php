<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultistepreg\Block\Adminhtml\Steps;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'multistep_step';
        $this->_blockGroup = 'ced_csMultistepreg';
        $this->_controller = 'adminhtml_steps';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Step'));
        $this->buttonList->update('save', 'class', 'save primary');
        $this->buttonList->update(
            'save',
            'data_attribute',
            ['mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']]]
        );
        $this->addButton(
            'back',
            [
                   'label' => __('Back'),
                   'onclick' => 'setLocation(\'' . $this->getUrl('csvendorattribute/attributes/index/') . '\')',
                   'class' => 'back'
               ],
            -1
        );
    }

    /**
     * @param string $buttonId
     * @param array $data
     * @param int $level
     * @param int $sortOrder
     * @param string $region
     * @return \Magento\Backend\Block\Widget\Form\Container|void
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * Retrieve header text
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        return __('Manage Multistep Registation Steps');
    }

    /**
     * Retrieve URL for validation
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', ['_current' => true]);
    }

    /**
     * Retrieve URL for save
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            '*/*/saveSteps',
            ['_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab')]
        );
    }
}
