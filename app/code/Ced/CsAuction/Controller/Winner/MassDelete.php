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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Controller\Winner;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class MassDelete
 *
 * @package Ced\CsAuction\Controller\Winner
 */
class MassDelete extends \Ced\CsMarketplace\Controller\Vproducts
{
    /**
     * @var \Ced\Auction\Model\WinnerFactory
     */
    protected $winnerFactory;

    /**
     * MassDelete constructor.
     *
     * @param \Ced\Auction\Model\WinnerFactory                             $winnerFactory
     * @param Context                                                      $context
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory
     * @param Session                                                      $customerSession
     * @param UrlFactory                                                   $urlFactory
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory             $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data                               $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl                                $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory                       $vendor
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Catalog\Model\ProductFactory                        $productFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory                    $vproductsFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     */
    public function __construct(
        \Ced\Auction\Model\WinnerFactory $winnerFactory,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
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
            $vendor,
            $storeManager,
            $productFactory,
            $vproductsFactory,
            $type
        );

        $this->winnerFactory = $winnerFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $vendorId = $this->_getSession()->getVendorId();
        if(!$vendorId) {
            return $this->_redirect('csauction/winner/index');
        }
        $entity_ids = explode(',', $this->getRequest()->getParam('product_id'));
        $redirectBack = false;

        if (!is_array($entity_ids) || empty($entity_ids)) {
            $this->messageManager->addErrorMessage(__('Please select Products(s).'));
        } else {
            $auctionDeleted = 0;
            try {
                foreach ($entity_ids as $entityId) {
                    $auction = $this->winnerFactory->create()
                        ->load($entityId);
                    $auction->delete();
                    $auctionDeleted++;
                }
            } catch (\Exception $e) {
                $redirectBack = true;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $auctionDeleted)
        );
        return $this->_redirect('csauction/*/index');
    }
}
