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
 * @category  Ced
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class IncrementPrice extends AbstractFieldArray
{
    public function _prepareToRender()
    {
        $this->addColumn('pricefrom', ['label' => __('Bid from'), 'size' => '30px', 'class' => 'required-entry']);
        $this->addColumn('priceto', ['label' => __('Bid to'), 'size' => '30px', 'class' => 'required-entry']);
        $this->addColumn('incrementedprice', ['label' => __('Bid increment'), 'size' => '30px', 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Price');
    }
}
