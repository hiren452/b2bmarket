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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Model\System\Config\Customer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\Helper\Context;

/**
 * Class Groups
 * @package Ced\RequestToQuote\Model\System\Config\Customer
 */
class Groups extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Groups constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        $this->_context = $context;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param bool $defaultValues
     * @param bool $withEmpty
     * @param null $storeId
     * @return array
     */
    public function toOptionArray($defaultValues = false, $withEmpty = false, $storeId=null)
    {
        $attributes = $this->collectionFactory->create()->toOptionArray();
        $options = [];
        $options[] = ['value' => -1, 'label' => __('Please Select')];
        foreach($attributes as $key => $value) {
            $options[] = ['value' => $value['value'], 'label' => $value['label']];
        }
        return $options;
    }

}
