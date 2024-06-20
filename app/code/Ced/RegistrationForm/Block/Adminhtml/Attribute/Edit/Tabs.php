<?php
namespace Ced\RegistrationForm\Block\Adminhtml\Attribute\Edit;

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
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('register_form');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Registration Form Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'information',
            [
                'label'     => __('General'),
                'content'   => $this->getLayout()->createBlock('Ced\RegistrationForm\Block\Adminhtml\Attribute\Edit\Tab\Main')->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
