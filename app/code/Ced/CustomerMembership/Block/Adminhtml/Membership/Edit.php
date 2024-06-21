<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Customermembership
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CustomerMembership\Block\Adminhtml\Membership;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Ced_CustomerMembership';
        $this->_controller = 'adminhtml_Membership';

        parent::_construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');

            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->addButton(
                    'close',
                    [
                    'label'     => __('Close Window'),
                            'class'     => 'cancel',
                            'onclick'   => 'window.close()',
                            'level'     => -1
                        ],
                    -100
                );
            }
        } else {

            $this->addButton(
                'save_and_edit_button',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                    'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                    ]
                    ]
            );

        }

        $this->buttonList->remove('reset');

        $this->buttonList->update('save', 'label', __('Save'));
    }

    /**
     * {@inheritdoc}
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {

        return $this->getUrl(
            'cmembership/cmembership/save',
            ['_current' => true, 'back' => null, 'id' => $this->getRequest()->getParam('id')]
        );
    }
}
