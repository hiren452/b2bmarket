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
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdatePost
 * @package Ced\RequestToQuote\Controller\Cart
 */
class UpdatePost extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CollectionFactory
     */
    protected $requestQuoteCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * UpdatePost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $requestQuoteCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $requestQuoteCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $items = $this->getRequest()->getParam('item');
        $quoteItems = $this->requestQuoteCollectionFactory->create()
                ->addFieldToFilter('customer_id', $this->session->getCustomerId())
                ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId());
        $resultRedirect->setPath('requesttoquote/cart/index');
        if ($items && $quoteItems) {
            if ($this->getRequest()->getParam('update_quote_action') == 'update_qty') {
                foreach ($quoteItems as &$item) {
                    if (isset($items[$item->getProductId()])) {
                        $item->setQuoteQty($items[$item->getProductId()]['qty']);
                        $item->setQuotePrice($items[$item->getProductId()]['price']);
                        $item->save();
                    }
                }
                $this->messageManager->addSuccessMessage(__('Quote cart has been updated successfully.'));
            }
            if ($this->getRequest()->getParam('update_quote_action') == 'empty_cart') {
                $quoteItems->walk('delete');
                $this->messageManager->addSuccessMessage(__('Quote cart has been cleared successfully.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something wen\'t wrong, Please try again.'));
        }
        return $resultRedirect;
    }
}
