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

namespace Ced\RequestToQuote\Observer;

use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\PoDetail\CollectionFactory as PoDetailCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Test
 * @package Ced\RequestToQuote\Observer
 */
class CollectTotal implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $poCollectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PoDetailCollectionFactory
     */
    protected $poDetailCollectionFactory;

    /**
     * Test constructor.
     * @param RequestInterface $request
     * @param CollectionFactory $poCollectionFactory
     * @param PoDetailCollectionFactory $poDetailCollectionFactory
     */
    public function __construct(
        RequestInterface $request,
        CollectionFactory $poCollectionFactory,
        PoDetailCollectionFactory $poDetailCollectionFactory
    ) {
        $this->poCollectionFactory = $poCollectionFactory;
        $this->request = $request;
        $this->poDetailCollectionFactory = $poDetailCollectionFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quoteid = $this->request->getParam('po_incId');
        $enabled = $this->poCollectionFactory->create()->addFieldToFilter('po_increment_id', $quoteid)
                    ->addFieldToFilter('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED)
                    ->getData();
        if(count($enabled) > 0) {

            $items = $quote->getAllItems();
            foreach($items as $item) {
                $quote = $this->poDetailCollectionFactory->create()->addFieldToFilter('po_id', $quoteid)->addFieldToFilter('product_id', $item->getProductId())->getData();
                if(!empty($quote[0])) {
                    $price=$quote[0]['po_price'];
                    $new_price = $quote[0]['po_price'] / $quote[0]['product_qty'];
                    $item = ($item->getParentItem() ? $item->getParentItem() : $item);
                    $item->setCustomPrice($new_price);
                    $item->setOriginalCustomPrice($new_price);
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
