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
namespace Ced\RequestToQuote\Controller\Quotes;

use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;

class Delete extends Action
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var CollectionFactory
     */
    protected $requestQuoteCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param RawFactory $resultRawFactory
     * @param CollectionFactory $requestQuoteCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        RawFactory $resultRawFactory,
        CollectionFactory $requestQuoteCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->resultRawFactory = $resultRawFactory;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result['success'] = '0';
        try {
            if ($this->session->isLoggedIn()) {
                if ($product_id = $this->getRequest()->getParam('productid')) {
                    $currentQuoteItem = $this->requestQuoteCollectionFactory->create()
                        ->addFieldToFilter('product_id', $product_id)
                        ->addFieldToFilter('customer_id', $this->session->getCustomer()->getId())
                        ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId())
                        ->getFirstItem();
                    if ($currentQuoteItem && $currentQuoteItem->getId()) {
                        $currentQuoteItem->delete();
                    }
                }
                $result['success'] = '1';
                $result['html'] = "<div class=\"custom_login\">
                    <button type=\"button\" class=\"action close\" title=\"" . __('Close') . "\">
                        <span>" . __('Close') . "</span>
                    </button>
                </div>
                <div class=\"Ced-rfq-cart-empty\">
                    <strong class=\"subtitle empty\">
                        " . __('You have no item in your quote cart.') . "
                    </strong>
                </div>";

            }
        } catch (\Exception $e) {

        }
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }
}
