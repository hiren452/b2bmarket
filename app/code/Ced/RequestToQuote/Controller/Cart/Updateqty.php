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

namespace Ced\RequestToQuote\Controller\Cart;

use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Updateqty
 * @package Ced\RequestToQuote\Controller\Cart
 */
class Updateqty extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    protected $requestQuoteCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Updateqty constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $resultjson
     * @param CollectionFactory $requestQuoteCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $resultjson,
        CollectionFactory $requestQuoteCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->resultJsonFactory = $resultjson;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $response['error'] = 0;
        $response['logout'] = 0;
        try {
            if (!$this->session->isLoggedIn()) {
                $this->messageManager->addErrorMessage(__('Please login first'));
                $response['logout'] = 1;
                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData($response);
            }
            $data = $this->getRequest()->getParams();
            if (isset($data['productid']) && $data['productid'] && isset($data['qty']) && $data['qty']) {
                $currentQuoteItem = $this->requestQuoteCollectionFactory->create()
                    ->addFieldToFilter('product_id', $data['productid'])
                    ->addFieldToFilter('customer_id', $this->session->getCustomer()->getId())
                    ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
                    ->getFirstItem();
                if ($currentQuoteItem && $currentQuoteItem->getId()) {
                    $currentQuoteItem->setQuoteQty($data['qty'])->save();
                }
            } else {
                $this->messageManager->addErrorMessage(__('Something wen\'t wrong, Please try again.'));
                $response['error'] = 1;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something wen\'t wrong, Please try again.'));
            $response['error'] = 1;
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
