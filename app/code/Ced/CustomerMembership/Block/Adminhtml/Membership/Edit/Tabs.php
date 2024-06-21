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
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Block\Adminhtml\Membership\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('membership_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Membership Package'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {

        $this->addTab(
            'membership_pacakge',
            [
               'label' => __('Package Information'),
               'title' => __('Package Information'),
               'content' => $this->getLayout()->createBlock('Ced\CustomerMembership\Block\Adminhtml\Membership\Edit\Tab\Package')->toHtml(),

               ]
        );

        return parent::_beforeToHtml();
    }
}
