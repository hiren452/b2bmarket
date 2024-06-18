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
namespace Ced\RequestToQuote\Controller\Customer;

use Ced\RequestToQuote\Model\PoDetailFactory;
use Ced\RequestToQuote\Model\PoFactory;
use Ced\RequestToQuote\Model\QuoteDetailFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Magento\Framework\App\Action\Context;

/**
 * Class Pocancel
 * @package Ced\RequestToQuote\Controller\Customer
 */
class Pocancel extends \Magento\Framework\App\Action\Action
{

    /**
     * @var QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var PoDetailFactory
     */
    protected $_poDetailFactory;

    /**
     * @var QuoteDetailFactory
     */
    protected $_quoteDetailFactory;

    /**
     * Pocancel constructor.
     * @param Context $context
     * @param QuoteFactory $quoteFactory
     * @param PoFactory $poFactory
     * @param PoDetailFactory $poDetailFactory
     * @param QuoteDetailFactory $quoteDetailFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        PoFactory $poFactory,
        PoDetailFactory $poDetailFactory,
        QuoteDetailFactory $quoteDetailFactory,
        array $data = []
    ) {
        $this->_quoteFactory = $quoteFactory;
        $this->_poFactory = $poFactory;
        $this->_poDetailFactory = $poDetailFactory;
        $this->_quoteDetailFactory = $quoteDetailFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('requesttoquote/customer/po');
        try {
            $poIncid = $this->getRequest()->getParam('po_id');
            $poData = $this->_poFactory->create()->load($poIncid, 'po_increment_id');
            $status = $poData->getStatus();
            if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING || $status == \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED) {
                $poData = $poData->setStatus(\Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING);
                $quoteId = $poData->getQuoteId();
                $quoteData = $this->_quoteFactory->create()->load($quoteId);
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                $poData->save();
                $quoteData->save();
                $po_items = $this->_poDetailFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('po_id', $poIncid);
                $remaining_qty = 0;
                $quote_id = 0;
                foreach ($po_items as $po_item) {
                    $quote_id = $po_item->getQuoteId();
                    $quote_detail = $this->_quoteDetailFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('quote_id', $po_item->getQuoteId())
                        ->addFieldToFilter('product_id', $po_item->getProductId())
                        ->getData()[0];
                    $rem = $quote_detail['remaining_qty'];
                    $q_id = $quote_detail['id'];
                    $rem1 = $rem + $po_item->getProductQty();
                    $remaining_qty += $po_item->getProductQty();
                    $this->_quoteDetailFactory->create()
                        ->load($q_id)
                        ->setRemainingQty($rem1)
                        ->save();
                }
                $quote_remanining = $quoteData->getRemainingQty();
                $quote_remanining += $remaining_qty;
                $quoteData->setRemainingQty($quote_remanining)->save();
                $this->messageManager->addErrorMessage(__('Po ' . $poIncid . ' was successfully cancelled.'));
            } else {
                if($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_DECLINED) {
                    $this->messageManager->addErrorMessage(__('PO ' . $poIncid . ' is already cancelled.'));
                } elseif ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_ORDERED) {
                    $this->messageManager->addErrorMessage(__('PO ' . $poIncid . ' has already been ordered.'));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error Occured, please try again'));
        }

    }
}
