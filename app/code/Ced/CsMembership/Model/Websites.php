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
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Model;

/**
 * Class Websites (for membership website options)
 */
class Websites implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Websites constructor.
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $websites = [];
        $website_collection = $this->collectionFactory->create();
        foreach ($website_collection as $website) {
            array_push($websites, ['value' => $website->getId(), 'label' => __($website->getName())]);
        }
        return $websites;
    }
}
