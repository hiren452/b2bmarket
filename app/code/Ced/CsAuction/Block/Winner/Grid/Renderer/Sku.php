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

namespace Ced\CsAuction\Block\Winner\Grid\Renderer;

class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_vproduct;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Ced\CsMarketplace\Model\Vproducts $vproduct,
        \Magento\Customer\Model\CustomerFactory $customer,
        array $data = []
    ) {
        $this->_vproduct = $vproduct;
        $this->customer = $customer;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $sku = $this->_vproduct->load($row->getProductId(), 'product_id')->getSku();
        return $sku;
    }
}
