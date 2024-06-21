<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CustomerMembership\Controller\Adminhtml\Csubscription;

use Ced\CustomerMembership\Model\ResourceModel\Subscription\CollectionFactory;
use Ced\CustomerMembership\Model\SubscriptionFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Catalog\Controller\Adminhtml\Product\MassStatus
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;
    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function _construct(
        \Magento\Backend\App\Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SubscriptionFactory $subscriptionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionFactory = $subscriptionFactory;
    }
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $poIds = $collection->getAllIds();
        $poupdated = 0;
        foreach ($poIds as $key => $value) {
            $model = $this->subscriptionFactory->create()->load($value);
            $model->delete();
            $poupdated++;
        }
        $this->messageManager->addSuccess(__($poupdated . ' Subscription Deleted successsfully.'));
        $this->_redirect('*/*/');
    }
}
