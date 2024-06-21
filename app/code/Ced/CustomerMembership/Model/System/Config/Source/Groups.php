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
 * @package     Ced_CustomerMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CustomerMembership\Model\System\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\StoreManagerInterface;

class Groups extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_context;
    protected $_storeManger;
    protected $_producttype;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\Type $producttype,
        StoreManagerInterface $storeManager,
        Collection $customerCollection
    ) {
        $this->storeManager = $storeManager;
        $this->_context = $context;
        $this->_producttype = $producttype;
        $this->customerCollection = $customerCollection;
    }

    public function toOptionArray($defaultValues = false, $withEmpty = false, $storeId = null)
    {
        $filter_a = ['null' => true];
        $filter_b = ['like' => '%configurable%'];
        $attributes = [];
        $attributes = $this->customerCollection->toOptionArray();

        $options = [];

        foreach ($attributes as $key => $value) {
            if ($value['value']!=0) {
                $options[] = ['value' => $value['value'], 'label' => $value['label']];
            }
        }
        return $options;
    }
}
