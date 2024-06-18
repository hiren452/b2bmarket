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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Block\Adminhtml\Widget;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Backend grid widget block
 *
 * @api
 * @deprecated                            100.2.0 in favour of UI component implementation
 * @method                                string getRowClickCallback() getRowClickCallback()
 * @method                                \Magento\Backend\Block\Widget\Grid setRowClickCallback() setRowClickCallback(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since                                 100.0.2
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        Status $status,
        Visibility $visibility,
        array $data = []
    ) {

        $this->_backendHelper = $backendHelper;
        $this->_backendSession = $context->getBackendSession();
        $this->collectionFactory = $collectionFactory;
        $this->visibility = $visibility;
        $this->status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    public function setCollection($collection)
    {
        $this->setData('dataSource', $collection);
    }

    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        $productId = [];
        $auctions = $this->collectionFactory->create()
            ->addFieldToFilter('status', ['processing','disapprove','not started']);

        if ($auctions->getData() != null) {
            foreach ($auctions as $auction) {
                array_push($productId, $auction['product_id']);
            }

            return $this->getData('dataSource')->addAttributeToFilter('entity_id', ['nin' => $productId])
                ->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
                ->addAttributeToFilter('visibility', ['neq'=>\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
                ->addAttributeToSelect('*');
        }
        return $this->getData('dataSource')
            ->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->addAttributeToFilter('visibility', ['neq'=>\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
            ->addAttributeToSelect('*');
    }
}
