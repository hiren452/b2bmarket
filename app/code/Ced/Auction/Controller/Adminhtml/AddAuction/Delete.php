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

namespace Ced\Auction\Controller\Adminhtml\AddAuction;

use Ced\Auction\Model\ResourceModel;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    public $filter;

    /**
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * Delete constructor.
     *
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ResourceModel\Auction\CollectionFactory $collectionFactory,
        ResourceModel\BidDetails\CollectionFactory $bidCollection
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->bidCollection = $bidCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->delete();
            $bid = $this->bidCollection->create()->addFieldToFilter('product_id', $item['product_id'])
                ->addFieldToFilter('status', 'bidding')->getLastItem();

            if(count($bid->getData())!= null) {
                $bid->setData('status', 'biddingClosed');
                $bid->save();

            }

        }

        $this->messageManager->addSuccessMessage(__('A total of %1 element(s) have been deleted.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('auction/addauction/index');
    }

    public function _isAllowed()
    {
        return true;
    }
}
