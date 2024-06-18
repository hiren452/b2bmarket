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

use Ced\RequestToQuote\Model\RequestQuoteFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var RequestQuoteFactory
     */
    protected $requestQuoteFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param RequestQuoteFactory $requestQuoteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        RequestQuoteFactory $requestQuoteFactory,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->requestQuoteFactory = $requestQuoteFactory;
        $this->redirect = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $productname = '';
        if ($data = $this->getRequest()->getPostValue()) {
            $currentCustomer = $this->session->getCustomer();
            $product = $this->productFactory->create()->load($data['product_id']);
            if ($product && $product->getId()) {
                $productname = $product->getName();
                $currentStoreId = $this->_storeManager->getStore()->getId();
                $currentQuoteItem = $this->requestQuoteFactory->create();
                $currentQuoteItem->setProductId($data['product_id']);
                $currentQuoteItem->setCustomerId($currentCustomer->getId());
                $currentQuoteItem->setCustomerEmail($currentCustomer->getEmail());
                $currentQuoteItem->setVendorId($data['vendor_id']);
                $currentQuoteItem->setStoreId($currentStoreId);
                $currentQuoteItem->setQuoteQty(round(trim($data['quote_qty'])));
                $currentQuoteItem->setQuotePrice(round(trim($data['quote_price'])));
                $currentQuoteItem->setProductType($product->getTypeId());
                $currentQuoteItem->setName($product->getName());
                $currentQuoteItem->setSku($product->getSku());
                if (isset($data['custom_option']) && $data['custom_option'] && $product->getTypeId() == 'configurable') {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                    $currentQuoteItem->setCustomOption(base64_decode($data['custom_option']));
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
                }
                $currentQuoteItem->save();
            }
            $this->messageManager->addSuccessMessage(__('You added %1 to your quote cart.', $productname));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect;

    }
}
