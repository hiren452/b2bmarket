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

namespace Ced\Auction\Model;

/**
 * Class Auction
 *
 * @package Ced\Auction\Model
 */
class BidDetails extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Auction constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Magento\Framework\Model\AbstractModel::_construct()
     */
    public function _construct()
    {
        $this->_init('Ced\Auction\Model\ResourceModel\BidDetails');
    }

    /**
     * Loading Bid Details
     *
     * @param  int  $key
     * @param  null $field
     * @return $this|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load($key, $field = null)
    {
        if ($field === null) {
            $this->_getResource()->load($this, $key, 'id');
            return $this;
        }
        $this->_getResource()->load($this, $key, $field);
        return $this;
    }
}
