<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Observer;

use Ced\CustomerMembership\Model\MembershipFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class VproductCreate implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @param RequestInterface $request
     * @param ManagerInterface $messageManager
     * @param ProductFactory $productFactory
     * @param MembershipFactory $membershipFactory
     */
    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        ProductFactory $productFactory,
        MembershipFactory $membershipFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->membershipFactory = $membershipFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $mode = $observer->getEvent()->getMode();
        $postdata = $observer->getEvent()->getPostdata();
        $result = $observer->getEvent()->getResult();
        $productFactory = $this->productFactory->create();

        switch ($mode) {
            case 'new':
                try {
                    // Create the virtual product for the membership
                    $websiteId = 1;
                    $storeId = 1;
                    $sku = 'customermembership' . rand();
                    $productFactory->setStoreId($storeId);
                    $productFactory->setWebsiteIds([$websiteId]);
                    $productFactory->setName($postdata['plan_name']);
                    $productFactory->setSku($sku);
                    $productFactory->setUrlKey($sku);
                    $productFactory->setAttributeSetId(4);
                    $productFactory->setTypeId('virtual');
                    $productFactory->setDescription($postdata['description']);
                    $productFactory->setShortDescription($postdata['description']);
                    $productFactory->setPrice($postdata['package_price']);
                    $productFactory->setVisibility(2);
                    $productFactory->setStatus(1);

                    if (!$productFactory->getIdBySku($sku)) {
                        $productFactory->setStockData([
                            'use_config_manage_stock' => 1,
                            'qty' => 100,
                            'min_qty' => 1,
                            'use_config_min_qty' => 1,
                            'min_sale_qty' => 1,
                            'use_config_min_sale_qty' => 1,
                            'max_sale_qty' => 100,
                            'use_config_max_sale_qty' => 1,
                            'is_qty_decimal' => 0,
                            'backorders' => 1,
                            'notify_stock_qty' => 1,
                            'is_in_stock' => 1,
                        ]);
                    }

                    $productFactory->setTaxClassId(0);
                    if (!$productFactory->getIdBySku($sku)) {
                        $productFactory->save();
                    }

                    $id = $productFactory->getIdBySku($sku);
                    $result->setResult($id);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
                break;

            case 'edit':
                $editId = $observer->getEvent()->getEditid();
                $membership = $this->membershipFactory->create()->load($editId);
                $product = $this->productFactory->create()->load($membership->getProductId());
                $product->setName($postdata['plan_name']);
                $product->setDescription($postdata['description']);
                $product->setShortDescription($postdata['description']);
                $product->setPrice($postdata['package_price']);
                try {
                    $product->save();
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
                break;
        }
    }
}
