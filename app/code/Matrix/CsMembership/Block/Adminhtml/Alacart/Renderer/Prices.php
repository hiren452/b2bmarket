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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Matrix\CsMembership\Block\Adminhtml\Alacart\Renderer;

class Prices extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Category constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return 'Click to View/Edit';
        //$category_array = array_unique(explode(',', $catId_json));
        /*$html = '<span>';
        foreach ($category_array as $value) {

            if ($_cat->getLevel() == '0' || $_cat->getLevel() == '1')
                continue;
            $html = $html . $_cat->getName() . '</br>';
        }*/
        return $category_array;
    }
}
