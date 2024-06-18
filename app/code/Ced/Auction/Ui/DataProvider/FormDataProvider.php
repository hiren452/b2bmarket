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
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Ui\DataProvider;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;

class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    public $loadedData;

    /**
     * FormDataProvider constructor.
     *
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Http              $request
     * @param ProductRepository $productRepository
     * @param Registry          $registry
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Http $request,
        ProductRepository $productRepository,
        Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->product = $productRepository;
        $this->registry = $registry;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {

        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        if ($this->registry->registry('product_id')) {

            $product = $this->product->getById($this->registry->registry('product_id'));
            $this->loadedData[$this->registry->registry('product_id')]['product_name'] = $product->getName();
            $this->loadedData[$this->registry->registry('product_id')]['product_price'] = $product->getFinalPrice();
            return $this->loadedData;
        }

        $id = $this->request->getParam('id');

        $product = $this->collection->addFieldToFilter('id', ['eq' => $id])->getData();
        if (isset($product[0])) {
            $productDetails = $this->product->getById($product[0]['product_id']);
            $items = $this->collection->getItems();
            foreach ($items as $auction) {
                $auction->setData('product_price', $productDetails->getFinalPrice());
                $auction->setData('start_datetime', $auction->getTempStartdate());
                $auction->setData('end_datetime', $auction->getTempEnddate());
                $this->loadedData[$auction->getId()] = $auction->getData();
            }
        }
        return $this->loadedData;
    }

    /**
     * @param  \Magento\Framework\Api\Filter $filter
     * @return mixed|null|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }
}
