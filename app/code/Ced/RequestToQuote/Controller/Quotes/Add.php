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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelperData;

    /**
     * Add constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param ResolverInterface $resolver
     * @param Escaper $escaper
     * @param Cart $cartHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        ResolverInterface $resolver,
        Escaper $escaper,
        Cart $cartHelper,
        JsonHelper $jsonHelper,
        LocalizedToNormalized $localizedToNormalized
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->resolver = $resolver;
        $this->escaper = $escaper;
        $this->productRepository = $productRepository;
        $this->cartHelper = $cartHelper;
        $this->jsonHelperData = $jsonHelper;
        $this->localizedToNormalized = $localizedToNormalized;
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function _initProduct()
    {
        $product_id = (int)$this->getRequest()->getParam('product');
        if ($product_id) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($product_id, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @return Add|\Magento\Framework\App\ResponseInterface|
     * \Magento\Framework\Controller\Result\Redirect|
     * \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $data = $this->getRequest()->getParams();
        try {
            if (isset($data['qty'])) {
                $filter = $this->localizedToNormalized;
                $data['qty'] = $filter->filter($data['qty']);
            }
            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            if (!$product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $data);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }
            $this->cart->save();
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->escaper->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->escaper->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $url = $resultRedirect->setPath('customer/account/login');
            }
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add this item to your shopping cart right now.'));
            return $this->goBack();
        }
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }
        $this->getResponse()->representJson(
            $this->jsonHelperData->jsonEncode($result)
        );
    }
}
