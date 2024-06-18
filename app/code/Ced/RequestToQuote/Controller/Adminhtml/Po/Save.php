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

namespace Ced\RequestToQuote\Controller\Adminhtml\Po;

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Po;
use Ced\RequestToQuote\Model\PoDetail;
use Ced\RequestToQuote\Model\PoDetailFactory;
use Ced\RequestToQuote\Model\QuoteDetailFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Save
 * @package Ced\RequestToQuote\Controller\Adminhtml\Po
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Po
     */
    protected $_po;

    /**
     * @var QuoteFactory
     */
    protected $_quote;

    /**
     * @var QuoteDetailFactory
     */
    protected $_quotedetail;

    /**
     * @var PoDetail
     */
    protected $_podetail;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var PoDetailFactory
     */
    protected $poDetailFactory;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    private $poPrefixHelper;

    /**
     * Save constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Po $po
     * @param QuoteFactory $quote
     * @param QuoteDetailFactory $quotedetail
     * @param PoDetail $podetail
     * @param PoDetailFactory $poDetailFactory
     * @param QuoteStatus $quoteStatus
     * @param CustomerFactory $customerFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Po $po,
        QuoteFactory $quote,
        QuoteDetailFactory $quotedetail,
        PoDetail $podetail,
        PoDetailFactory $poDetailFactory,
        QuoteStatus $quoteStatus,
        CustomerFactory $customerFactory,
        Data $helper,
        \MageMonkeys\PoPrefix\Helper\Data $poPrefixHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_po = $po;
        $this->_quote = $quote;
        $this->_quotedetail = $quotedetail;
        $this->_podetail = $podetail;
        $this->helper = $helper;
        $this->poDetailFactory = $poDetailFactory;
        $this->quoteStatus = $quoteStatus;
        $this->customerFactory = $customerFactory;
        $this->poPrefixHelper = $poPrefixHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom1.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('text message');

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->helper->isEnable() == '0') {
            $this->messageManager->addErrorMessage(__('Please Enable the extension.'));
            $resultRedirect->setPath('*/*/*');
        }
        $resultRedirect->setPath('requesttoquote/po/index');
        $data = $this->getRequest()->getPostValue();
        $quote_id = $data['quote_id'];
        $quoteData = $this->_quote->create()->load($quote_id);
        $customer_id = $quoteData->getCustomerId();
        $customerName = '';
        $customer = $this->customerFactory->create()->load($customer_id);
        if ($customer && $customer->getId()) {
            $customerName = $customer->getName();
        }
        $qty = $quoteData->getQuoteUpdatedQty();
        $price = $quoteData->getQuoteUpdatedPrice();
        $store_id = $quoteData->getStoreId();
        $quotetotalproducts = 0;
        $po_qty = 0;
        $pocollection = $this->_po->getCollection();
        try {
            if (count($pocollection) > 0) {
                $po_id = $pocollection->getLastItem()->getPoId();
                $po_id++;
                $poincId = $this->poPrefixHelper->getPoPrefix('request_to_quote') . $store_id . $po_id;
            } else {
                $poincId = $this->poPrefixHelper->getPoPrefix('request_to_quote') . $store_id . '01';
            }
            $data['grandtotalofpo'] = 0;
            $data['subtotalofpo'] = 0;
            $baseUrl = $this->_url->getBaseUrl();
            $link = $baseUrl . 'requesttoquote/quotes/addtocart/po_incId/' . $poincId . '/id/' . $quote_id;
            $item_info = [];
            foreach ($data['item'] as $key => $value) {
                $quoteItem = $this->_quotedetail->create()->load($key);
                if (!($quoteItem->getQuoteUpdatedQty() && $quoteItem->getQuoteUpdatedQty() > 0)) {
                    $quoteItem->setQuoteUpdatedQty($quoteItem->getProductQty())->save();
                }
                if (!($quoteItem->getUnitPrice() && $quoteItem->getUnitPrice() > 0)) {
                    $quoteItem->setUnitPrice($quoteItem->getPrice())->save();
                }
                $po_detail = $this->poDetailFactory->create();
                $po_detail->setData('po_id', $poincId);
                $po_detail->setData('quote_id', $quote_id);
                $po_detail->setData('product_id', $quoteItem->getProductId());
                $po_detail->setData('product_qty', $value);
                $qqty = $quoteItem->getQuoteUpdatedQty();
                $po_detail->setData('parent_id', $quoteItem->getParentId());
                $po_detail->setData('custom_option', $quoteItem->getCustomOption());
                if($qqty != $value) {
                    $quoteItem->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                }
                $qprice = $quoteItem->getPrice();
                $po_detail->setData('quoted_qty', $qqty);
                $po_detail->setData('quoted_price', $qprice);
                if ($value < $qqty) {
                    if($quoteItem->getRemainingQty()) {
                        $po_detail->setData('product_qty', $value);
                        $remqty = $quoteItem->getRemainingQty() - $value;
                        $quoteItem->setRemainingQty($remqty);
                        $po_detail->setData('remaining_qty', $remqty);
                        $po_qty += $value;
                    } else {
                        $po_detail->setData('product_qty', $value);
                        $remqty = $qqty - $value;
                        $quoteItem->setRemainingQty($remqty);
                        $po_detail->setData('remaining_qty', $remqty);
                        $po_qty += $value;
                    }
                } else {
                    if ($quoteItem->getRemainingQty()) {
                        $po_detail->setData('product_qty', $quoteItem->getRemainingQty());
                        $po_qty += $quoteItem->getRemainingQty();
                        $quoteItem->setRemainingQty(0);
                        $po_detail->setData('remaining_qty', 0);
                    } else {
                        $po_detail->setData('product_qty', $qqty);
                        $quoteItem->setRemainingQty(0);
                        $po_detail->setData('remaining_qty', 0);
                        $po_qty += $qqty;
                    }
                }
                $data['grandtotalofpo'] += ($value * $qprice);
                $data['subtotalofpo'] += $data['grandtotalofpo'];
                $po_detail->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
                $po_detail->setData('product_type', $quoteItem->getProductType());
                $po_detail->setData('name', $quoteItem->getName());
                $po_detail->setData('sku', $quoteItem->getSku());
                $po_detail->setData('po_price', ($value * $qprice));
                $po_detail->setData('vendor_id', $quoteItem->getVendorId());
                $quoteItem->save();
                $po_detail->save();
                $quotetotalproducts = $quotetotalproducts + $value;
                $item_info[$key]['prod_id'] = $quoteItem->getProductId();
                $item_info[$key]['name'] = $quoteItem->getName();
                $item_info[$key]['qty'] = $qqty;
                $item_info[$key]['sku'] = $quoteItem->getSku();
                $item_info[$key]['price'] = $qprice;
            }

            if ($price > $data['grandtotalofpo']) {
                $remaining_price = $price - $data['grandtotalofpo'];
            } else {
                $remaining_price = $data['grandtotalofpo'] - $price;
            }
            $this->_po->setData('quote_id', $quote_id);
            $this->_po->setData('po_increment_id', $poincId);
            $this->_po->setData('quote_updated_qty', $qty);
            $this->_po->setData('quote_updated_price', $price);
            $this->_po->setData('po_qty', $po_qty);
            $this->_po->setData('po_price', $data['grandtotalofpo']);
            $this->_po->setData('remaining_price', $remaining_price);
            $this->_po->setData('po_customer_id', $customer_id);
            $this->_po->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
            $this->_po->setData('vendor_id', $quoteData->getVendorId());
            if ($qty == $quotetotalproducts ||
                (($quoteItem->getRemainingQty() == '0') &&
                ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO))
            ) {
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED);
            } elseif ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED) {
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED);
            } elseif (($quoteItem->getRemainingQty() === '0') && ($quoteData->getStatus() === \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO)) {
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED);
            } else {
                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO);
            }

            $quoteData->setRemainingQty((int) $quoteData->getRemainingQty() - (int) $po_qty);
            $quoteData->save();
            $this->_po->save();
            $email = $quoteData->getCustomerEmail();
            $message =  $this->helper->sendPoCreatedMail($quoteData->getQuoteIncrementId(), $poincId, $link, $customerName, $email, $store_id);
            $status = $quoteData->getStatus();
            $label = $this->quoteStatus->getFrontendOptionText($status);
            $totals['subtotal'] = $quoteData->getQuoteUpdatedPrice();
            $totals['shipping'] = $quoteData->getShippingAmount();
            $totals['grandtotal'] = $totals['subtotal'] + $totals['shipping'];
            $template = $this->helper->getConfigValue(Data::QUOTE_UPDATE_EMAIL);
            $template_variables = [
                'quote_id' => '#' . $quoteData->getQuoteIncrementId(),
                'quote_status' => $label,
                'item_info' => $item_info,
                'totals' => $totals,
                'name' => $customerName
            ];

            $this->helper->sendEmail($template, $email, $template_variables);
            $this->messageManager->addSuccessMessage(__('Po has been successfully created. ' . $message));

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the PO. Kindly enter the correct data.'));
        }
        return $resultRedirect;
    }
}
