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

namespace Ced\CsMembership\Model\Backend\System\Config;

/**
 * Class Category (for getting categories)
 */
class Category extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializerInterface;

    /**
     * Duration constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Serialize\SerializerInterface $serializerInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface,
        array $data = []
    ) {
        $this->_serializerInterface = $serializerInterface;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    /**
     * Process data after load
     */
    public function afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $arr   = $this->_serializerInterface->unserialize($value);

            if (!is_array($arr)) {
                return '';
            }

            $sortOrder = [];
            foreach ($arr as $k => $val) {
                if (!is_array($val)) {
                    unset($arr[$k]);
                    continue;
                }
                //$sortOrder[$k] = $val['priority'];
            }
            //sort by order
            // array_multisort($sortOrder, SORT_ASC, $arr);
            $this->setValue($arr);
        }
    }

    /**
     * Prepare data before save
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $exixting=[];
        foreach ($value as $key => $val) {
            if ($key=='__empty') {
                continue;
            }
            if (in_array(trim($val['category']), $exixting)) {
                unset($value[$key]);
            } else {
                array_push($exixting, trim($val['category']));
            }
        }
        $value = $this->_serializerInterface->serialize($value);
        $this->setValue($value);
    }
}
