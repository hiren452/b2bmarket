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

namespace Ced\CsMembership\Block\Adminhtml\Assign;

/**
 * Class Edit (for assigning membership)
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Construct for setting objectId, blockgroup, controller
     */
    public function _construct()
    {
        $this->_objectId = 'assign_id';
        $this->_blockGroup = 'Ced_CsMembership';
        $this->_controller = 'adminhtml_assign';

        parent::_construct();
        $this->buttonList->update('save', 'label', __('Assign Membership'));
    }

    /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Assign Membership');
    }
}
