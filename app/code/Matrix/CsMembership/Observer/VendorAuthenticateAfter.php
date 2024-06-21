<?php

namespace Matrix\CsMembership\Observer;

use Ced\CsMarketplace\Model\VproductsFactory;
use Ced\CsMembership\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;

class VendorAuthenticateAfter implements ObserverInterface
{
    protected $customHelper;

    protected $request;

    protected $responseFactory;

    protected $url;

    protected $messageManager;

    protected $membershipHelper;

    protected $cookieManager;

    protected $cookieMetadataFactory;

    protected $sessionManager;

    protected $vproductsFactory;

    public function __construct(
        \B2bmarkets\Custom\Helper\Data $customHelper,
        RequestInterface               $request,
        ResponseFactory                $responseFactory,
        UrlInterface                   $url,
        ManagerInterface               $messageManager,
        Data                           $membershipHelper,
        CookieManagerInterface         $cookieManager,
        CookieMetadataFactory          $cookieMetadataFactory,
        SessionManagerInterface        $sessionManager,
        VproductsFactory               $vproductsFactory
    ) {
        $this->customHelper = $customHelper;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->messageManager = $messageManager;
        $this->membershipHelper = $membershipHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->vproductsFactory = $vproductsFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        //$vendor = $observer->getEvent()->getSession()->getVendor();

        $route = strtolower($this->request->getRouteName());
        $controller = strtolower($this->request->getControllerName());
        $action = strtolower($this->request->getActionName());

        $path = $route . '/' . $controller . '/' . $action;

        /* Check subscription has taken or not */
        $urlToCheck = ['csauction/auctionlist/index', 'csauction/addauction/index', 'csauction/addauction/auctionform',
        'cscategorymap/category/select', 'csproduct/vproducts/index', 'csimportexport/import/index',
        'csshopify/vendor/category', 'csshopify/vendor/product', 'csamazonproductimporter/product/index','csproduct/vproducts/new'];
        $productlimit = ['cscategorymap/category/select', 'csimportexport/import/index',
            'csamazonproductimporter/product/index', 'csshopify/vendor/category', 'csshopify/vendor/product',
            'csproduct/vproducts/new'];

        if (in_array($path, $urlToCheck)) {
            $vendorId = $observer->getEvent()->getSession()->getVendorId();
            $existingSubscription = $this->membershipHelper->getExistingSubcription($vendorId);

            if (empty($existingSubscription)) {
                $this->messageManager->addNotice(__('Please take a subscription first.'));
                $url = $this->url->getUrl('subscription/membership/index');
                $this->responseFactory->create()->setRedirect($url)->sendResponse();
            }
            $Remainingcount = $existingSubscription[0]['product_limit'] - count($this->vproductsFactory->create()->getVendorProductIds($vendorId));
            if (in_array($path, $productlimit) && $Remainingcount <= 0) {
                $this->messageManager->addNotice(__('Your product limit is exceeded. Please upgrade your subscription'));
                $url = $this->url->getUrl('subscription/membership/index');
                $this->responseFactory->create()->setRedirect($url)->sendResponse();
            }
        }
        /* Check Post Auction Cookie */
        $post_auction_cookie = $this->cookieManager->getCookie('post_auction_redirect');
        if (!empty($post_auction_cookie)) {
            $this->deletePostAuctionRedirect();

            $url = $this->url->getUrl('cscategorymap/category/select');
            $this->responseFactory->create()->setRedirect($url)->sendResponse();
        }
        /* Check Post Auction Cookie */

        return $this;
    }

    public function deletePostAuctionRedirect()
    {
        $this->cookieManager->deleteCookie(
            'post_auction_redirect',
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }
}
