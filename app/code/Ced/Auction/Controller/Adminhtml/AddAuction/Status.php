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

namespace Ced\Auction\Controller\Adminhtml\AddAuction;

use Ced\Auction\Model\ResourceModel\Auction\Collection;
use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class Status extends \Magento\Backend\App\Action
{
    /**
     * Status constructor.
     *
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param Collection        $collection
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Collection $collection
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->collection = $collection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $changeStatus = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection as $rate) {
            $rate->setStatus($this->getRequest()->getParam('status'))->save();
            $rate->setSellproduct('yes')->save();
            $changeStatus++;
        }

        if ($changeStatus) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $changeStatus));
            $changeStatus = 0;
        }
        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('auction/addauction/index');

        return $resultRedirect;
    }

    public function _isAllowed()
    {
        return true;
    }
}
