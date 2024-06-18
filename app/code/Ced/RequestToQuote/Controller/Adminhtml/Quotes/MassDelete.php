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

namespace Ced\RequestToQuote\Controller\Adminhtml\Quotes;

use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package Ced\HubIntegration\Controller\Adminhtml\ErrorLog
 */
class MassDelete extends \Magento\Backend\App\Action
{

    /**
     * @var string
     */
    protected $redirectUrl = 'requesttoquote/quotes/view';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var object
     */
    protected $collectionFactory;

    /**
     * @var PoCollectionFactory
     */
    protected $poCollectionfactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param PoCollectionFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        PoCollectionFactory $poCollectionfactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->poCollectionfactory = $poCollectionfactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            foreach($collection as $item) {
                if ($item->getStatus() == '4') {
                    $collections = $this->poCollectionfactory->create()
                                      ->addFieldToFilter('quote_id', ['in' => $item->getId()]);
                    $totalCount = $collections->getSize();
                    $collections->walk('delete');
                }
            }
            $totalCount = $collection->getSize();
            $collection->walk('delete');
            $this->messageManager->addSuccessMessage(__("%1 Quote(s) have been deleted.", $totalCount));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath($this->redirectUrl);
    }
}
