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

namespace Ced\CsMembership\Plugin\Product;

/**
 * Class Collection (for getting product collection)
 */
class Collection
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * Collection constructor.
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\State $state
    ) {
        $this->_state = $state;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterAddAttributeToSort(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        $result
    ) {
        if ($this->_state->getAreaCode() == 'adminhtml') {
            $subject->addAttributeToFilter('sku', ['nlike' => '%membership%']);
        }
        return $result;
    }
}
