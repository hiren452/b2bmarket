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

/**
 * Class CancelPo
 * @package Ced\RequestToQuote\Controller\Customer
 */
class CancelPo extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Ced\RequestToQuote\Model\QuoteFactory
     */
    protected $_quote;

    /**
     * @var \Ced\RequestToQuote\Model\PoFactory
     */
    protected $_po;

    /**
     * @var \Ced\RequestToQuote\Model\PoDetailFactory
     */
    protected $poDetailFactory;

    /**
     * @var \Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory
     */
    protected $quoteDetailCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * CancelPo constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Ced\RequestToQuote\Model\QuoteFactory $quote
     * @param \Ced\RequestToQuote\Model\PoFactory $po
     * @param \Ced\RequestToQuote\Model\PoDetailFactory $poDetailFactory
     * @param \Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory $quoteDetailCollectionFactory
     * @param \Magento\Customer\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ced\RequestToQuote\Model\QuoteFactory $quote,
        \Ced\RequestToQuote\Model\PoFactory $po,
        \Ced\RequestToQuote\Model\PoDetailFactory $poDetailFactory,
        \Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory $quoteDetailCollectionFactory,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_quote = $quote;
        $this->_po = $po;
        $this->poDetailFactory = $poDetailFactory;
        $this->quoteDetailCollectionFactory = $quoteDetailCollectionFactory;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $poId = $this->getRequest()->getParam('po_id');
            $poData = $this->_po->create()->load($poId);
            if (!($poData && $poData->getId())) {
                $this->messageManager->addErrorMessage(__('This Po no longer exist.'));
                $resultRedirect->setPath('requesttoquote/customer/po');
                return $resultRedirect;
            }
            if ($poData->getPoCustomerId() != $this->session->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('This Po does not belongs to you.'));
                $resultRedirect->setPath('requesttoquote/customer/po');
                return $resultRedirect;
            }
            $poIncid = $poData->getPoIncrementId();
            $status = $poData->getStatus();
            if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING || $status == \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED) {
                $poData = $poData->setStatus(\Ced\RequestToQuote\Model\Po::PO_STATUS_DECLINED);
                $quoteId = $poData->getQuoteId();
                $quoteData = $this->_quote->create()->load($quoteId);
                if ($quoteData->getRemainingQty() == '') {
                    $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                } elseif ($poData->getPoQty() == $quoteData->getQuoteUpdatedQty()) {
                    $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                } elseif (($poData->getPoQty() + $quoteData->getRemainingQty()) ==  $quoteData->getQuoteUpdatedQty()) {
                    $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                } else {
                    $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO);
                }
                $poData->save();
                $quoteData->save();
                $po_items = $this->poDetailFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('po_id', $poIncid);
                $remaining_qty = 0;
                $quote_id = 0;
                foreach ($po_items as $po_item) {
                    $quote_id = $po_item->getQuoteId();
                    $quote_detail = $this->quoteDetailCollectionFactory->create()
                        ->addFieldToFilter('quote_id', $po_item->getQuoteId())
                        ->addFieldToFilter('product_id', $po_item->getProductId())
                        ->getFirstItem();
                    $rem = $quote_detail['remaining_qty'];
                    $rem1 = $rem + $po_item->getProductQty();
                    $remaining_qty += $po_item->getProductQty();
                    $quote_detail->setRemainingQty($rem1)->save();
                }
                $quote_remanining = $quoteData->getRemainingQty();
                $quote_remanining += $remaining_qty;
                $quote = $this->_quote->create()->load($quote_id);
                if ($quote && $quote->getId()) {
                    $quote->setRemainingQty($quote_remanining)->save();
                }
                $this->messageManager->addSuccessMessage(__('#%1 has been successfully cancelled.', $poIncid));
            } else {
                if ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_DECLINED) {
                    $this->messageManager->addErrorMessage(__('#%1 is already cancelled.', $poIncid));
                } elseif ($status == \Ced\RequestToQuote\Model\Po::PO_STATUS_ORDERED) {
                    $this->messageManager->addErrorMessage(__('#%1 has already been ordered.', $poIncid));
                }
            }
            $resultRedirect->setPath('requesttoquote/customer/editpo', ['poId' => $poId]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Soething wen\'t wrong please try again.'));
        }
        return $resultRedirect->setPath('requesttoquote/customer/editpo');
    }
}
