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

namespace Ced\Auction\Block\Adminhtml\ViewBid;

use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

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

    public function getCollection()
    {
        $id = $this->getRequest()->getParam('product_id');
        $start_date = $this->getRequest()->getParam('start_date');
        $end_date = $this->getRequest()->getParam('end_date');
        return $this->getData('dataSource')
            ->addFieldToFilter('product_id', $id)
            ->addFieldToFilter('bid_date', ['gteq'=>$start_date])
            ->addFieldToFilter('bid_date', ['lteq'=>$end_date]);
    }
}
