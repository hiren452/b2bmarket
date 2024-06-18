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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block;

class Winner extends \Magento\Backend\Block\Widget\Container
{
    public $_template = 'winner.phtml';
    /**
     * @var \Magento\Catalog\Model\Product\TypeFactory
     */
    public $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $_productFactory;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'winner';
        $this->_blockGroup = 'Ced_CsAuction';
        $this->_headerText = __('Products');
        parent::_construct();
    }

    /**
     * Product constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context      $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_typeFactory = $typeFactory;

        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Ced\CsAuction\Block\Winner\Grid', 'ced.csauction.vendor.auction.winner.grid')
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve product create url by specified product type
     *
     * @param  string $type
     * @return string
     */
    public function _getProductCreateUrl($type)
    {

        return $this->getUrl(
            'csauction/winner/index',
            ['set' => $this->_productFactory->create()->getDefaultAttributeSetId(), 'type' => $type]
        );
    }

    public function _getCreateUrl()
    {
        return $this->getUrl(
            'csauction/winner/index'
        );
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
