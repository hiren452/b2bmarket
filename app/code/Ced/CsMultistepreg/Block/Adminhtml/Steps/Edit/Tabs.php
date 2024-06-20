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
namespace Ced\CsMultistepreg\Block\Adminhtml\Steps\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Attribute Information'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'multistep_steps',
            [
               'label' => __('Add/Delete Steps'),
               'title' => __('Properties'),
               'content' => $this->getLayout()
                                 ->createBlock('\Ced\CsMultistepreg\Block\Adminhtml\Steps\Edit\Tab\Form')
                                 ->toHtml(),
               'active' => true
               ]
        );

        return parent::_beforeToHtml();
    }
}
