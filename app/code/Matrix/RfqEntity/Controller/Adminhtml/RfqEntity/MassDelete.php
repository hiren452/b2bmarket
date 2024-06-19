<?php
namespace Matrix\RfqEntity\Controller\Adminhtml\RfqEntity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Matrix\RfqEntity\Model\ResourceModel\RfqEntity\Collection;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Collection
     */
    protected $rfqentityCollection;

    /**
     * @param  Context    $context
     * @param  Filter     $filter
     * @param  Collection $rfqentityCollection
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Collection $rfqentityCollection
    ) {
        $this->filter = $filter;
        $this->rfqentityCollection = $rfqentityCollection;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->rfqentityCollection);
        $collectionSize = $collection->getSize();
        $collection->walk('delete');

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
