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

namespace Ced\CsMembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class CreateVirtual (set values before product save)
 */
class CreateVirtual implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * CreateVirtual constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param ManagerInterface $messageManager
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        ManagerInterface $messageManager,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->membershipFactory = $membershipFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute action
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postdata = $observer->getEvent()->getPostdata();

        $form_data = $this->request->getPost();
        $mode = $observer->getEvent()->getMode();
        switch ($mode) {
            case 'edit':
                $editid = $observer->getEvent()->getEditid();
                $product_id = $this->membershipFactory->create()->load($editid)->getProductId();
                $product = $this->productFactory->create()->load($product_id);
                $product->setStockData([
                    'manage_stock' => 0,
                    'is_in_stock' => 1,
                    'qty' => $postdata['qty'],
                    'sku'=>$postdata['name'],
                    'min_sale_qty' => 1,
                    'max_sale_qty' => 100])
                     ->setSku($postdata['name'])
                    ->setName($postdata['name'])
                    ->setShortDescription('description')
                    ->setDescription('description')
                    ->setPrice($postdata['price'])
                    ->setTaxClassId(2)// Taxable Goods by default
                    ->setWeight(10);
                // if ($postdata['special_price'] > 0)
                // $product->setSpecialPrice($postdata['special_price']);

                try {
                    $product->save();
                    $result = $observer->getEvent()->getResult();
                    $result->setResult($product->getId());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
                break;
            case 'new':
                try {
                    $result = $observer->getEvent()->getResult();
                    $sku = 'membership' . rand();
                    $product = $this->productFactory->create();
                    $product
                        ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
                        ->setAttributeSetId($this->productFactory->create()->getDefaultAttributeSetId())
                        ->setSku($sku)
                        ->setWebsiteIds([1])
                        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
                        ->setStockData([
                            'manage_stock' => 0,
                            'is_in_stock' => 1,
                            'qty' => $postdata['qty'],
                            'min_sale_qty' => 1,

                            'max_sale_qty' => 100
                        ])
                        ->setName($postdata['name'])
                        ->setShortDescription('Description')
                        ->setDescription('Description')
                        ->setPrice($postdata['price'])
                        ->setTaxClassId(2)// Taxable Goods by default
                        ->setWeight(10);

                    if ($postdata['image']) {
                        $product->addImageToMediaGallery($postdata['image'], ['image', 'small_image', 'thumbnail'], true, true);
                    }

                    if ($postdata['special_price'] > 0) {
                        $product->setSpecialPrice($postdata['special_price']);
                    }
                    try {
                        $product->save();
                        $product_id = $this->productFactory->create()->getIdBySku($sku);
                        $result->setResult($product_id);
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__($e->getMessage()));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
                break;
        }
        return $this;
    }
}
