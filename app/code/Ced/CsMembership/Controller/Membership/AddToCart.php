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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Controller\Membership;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class AddToCart (Add to cart membership product)
 */
class AddToCart extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * AddToCart constructor.
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendor
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );

        $this->membershipHelper = $membershipHelper;
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute action
     *
     * @return \Ced\CsMarketplace\Controller\ResponseInterface|
     * \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');

        $vendorId = $this->_getSession()->getVendorId();
        $model = $this->membershipHelper;
        $existing = $model->getExistingSubcription($vendorId);
        $existing_new = [];
        foreach ($existing as $value) {
            $existing_new[] = $value['subscription_id'];
        }
        if (in_array($id, $existing_new)) {
            $this->messageManager->addErrorMessage('You have already subscribed this package');
            $this->_redirect('*/*/index');
            return;
        } else {
            $productId = $this->membershipHelper->getProductIdByMembershipId($id);
            try {
                $product = $this->productRepository->getById($productId);
                $qty = 1;

                $session = $this->checkoutSession->create();
                $quote = $session->getQuote();
                $quote->addProduct($product, $qty);

                $this->cartRepository->save($quote);
                $session->replaceQuote($quote)->unsLastRealOrderId();
                $this->messageManager->addSuccessMessage('Membership package has been added to cart.');
                return $this->_redirect('checkout/cart/index');

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/index');
                return;
            }
        }
    }
}
