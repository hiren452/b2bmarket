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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Matrix\CsMembership\Block\Adminhtml;

class Alacart extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'alacart/alacart.phtml';

    /**
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
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Matrix\CsMembership\Block\Adminhtml\Alacart\Grid', 'ced.marketplace.vendor.membership.alacart.entity')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function getAddButtonOptions()
    {
        $splitButtonOptions = [
            'label' => __('Add New Membership Plan'),
            'class' => 'primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add', $splitButtonOptions);
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
