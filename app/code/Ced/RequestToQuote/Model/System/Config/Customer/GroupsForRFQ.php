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

class GroupsForRFQ extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * Groups constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $groupCollectionFactory
    ) {
        $this->_context = $context;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function toOptionArray($defaultValues = false, $withEmpty = false, $storeId=null)
    {
        $attributes = $this->groupCollectionFactory->create()->toOptionArray();
        $options = [];
        foreach($attributes as $key => $value) {
            /*skip for guest customers*/
            if ($value['value'] == 0) {
                continue;
            }

            array_push($options, ['label'=>$value['label'],'value'=>$value['value']]);
        }
        return $options;
    }
}
