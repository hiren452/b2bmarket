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
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Controller\Po;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data as CsMarketplaceHelperData;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsRfq\Helper\Data as CsRfqHelper;
use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Po;
use Ced\RequestToQuote\Model\PoDetail;
use Ced\RequestToQuote\Model\PoDetailFactory;
use Ced\RequestToQuote\Model\QuoteDetailFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Save
 * @package Ced\CsRfq\Controller\Adminhtml\Po
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    private $poPrefixHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param CsMarketplaceHelperData $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param StoreManagerInterface $storeManager
     * @param Po $po
     * @param QuoteFactory $quote
     * @param QuoteDetailFactory $quotedetail
     * @param PoDetail $podetail
     * @param PoDetailFactory $poDetailFactory
     * @param QuoteStatus $quoteStatus
     * @param Data $helper
     * @param CustomerFactory $customerFactory
     * @param CsRfqHelper $csRfqHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        CsMarketplaceHelperData $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        StoreManagerInterface $storeManager,
        Po $po,
        QuoteFactory $quote,
        QuoteDetailFactory $quotedetail,
        PoDetail $podetail,
        PoDetailFactory $poDetailFactory,
        QuoteStatus $quoteStatus,
        Data $helper,
        CustomerFactory $customerFactory,
        CsRfqHelper $csRfqHelper,
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
        $this->csRfqHelper = $csRfqHelper;
        $this->poPrefixHelper = $poPrefixHelper;
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
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->helper->isEnable() == '0') {
            $this->messageManager->addErrorMessage(__('Please Enable the extension.'));
            return $this->_redirect('*/*/*');
        }

        if (! $this->csRfqHelper->isVendorRfqEnable()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }
        $data = $this->getRequest()->getParams();
        $quote_id = $data['quote_id'];
        $quoteData = $this->_quote->create()->load($quote_id);
        $customer_id = $quoteData->getCustomerId();
        $qty = $quoteData->getQuoteUpdatedQty();
        $price = $quoteData->getQuoteUpdatedPrice();
        $store_id = $quoteData->getStoreId();
        $quotetotalproducts = 0;
        $po_qty = 0;
        $pocollection = $this->_po->getCollection();
        try {
            if (sizeof($pocollection) > 0) {
                $po_id = $pocollection->getLastItem()->getPoId();
                $po_id++;
                $poincId = $this->poPrefixHelper->getPoPrefix('csrfq') . $store_id . $po_id;
            } else {
                $poincId = $this->poPrefixHelper->getPoPrefix('csrfq') . $store_id . '01';
            }
            $data['grandtotalofpo'] = 0;
            $data['subtotalofpo'] = 0;
            $link = $this->_url->getUrl('requesttoquote/quotes/addtocart', ['po_incId' => $poincId]);
            $item_info = [];
            $cancel = $this->_url->getUrl('requesttoquote/customer/cancelpo', ['po_incId' => $poincId]);
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
                $po_detail->setData('vendor_id', $this->_getSession()->getVendorId());
                $po_detail->setData('product_id', $quoteItem->getProductId());
                $po_detail->setData('product_qty', $value);

                $qqty = $quoteItem->getQuoteUpdatedQty();
                $po_detail->setData('parent_id', $quoteItem->getParentId());
                $po_detail->setData('custom_option', $quoteItem->getCustomOption());
                if ($qqty != $value) {
                    $quoteItem->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                }
                $qprice = $quoteItem->getUnitPrice();
                $po_detail->setData('quoted_qty', $qqty);
                $po_detail->setData('quoted_price', $qprice);
                if ($value < $qqty) {
                    if ($quoteItem->getRemainingQty()) {
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
            $this->_po->setData('vendor_id', $this->_getSession()->getVendorId());
            $this->_po->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
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
            $quoteData->setRemainingQty((int)$quoteData->getRemainingQty() - (int)$po_qty);
            $quoteData->save();
            $this->_po->save();
            $customer = $this->customerFactory->create()->load($customer_id);
            if ($customer && $customer->getId()) {
                $customerName = $customer->getName();
            }
            $email = $quoteData->getCustomerEmail();
            /*$message =  $this->helper->sendPoCreatedMail($customer_id, $quoteData->getQuoteIncrementId(), $poincId, $link, $po_qty, $data['subtotalofpo'], $cancel);*/
            $message = $this->helper->sendPoCreatedMail($quoteData->getQuoteIncrementId(), $poincId, $link, $customerName, $email, $store_id);
            $status = $quoteData->getStatus();
            $label = $this->quoteStatus->getOptionText($status);

            $totals['subtotal'] = $quoteData->getQuoteUpdatedPrice();
            $totals['shipping'] = $quoteData->getShippingAmount();
            $totals['grandtotal'] = $totals['subtotal'] + $totals['shipping'];
            $template = $this->helper->getConfigValue(Data::QUOTE_UPDATE_EMAIL);
            $template_variables = ['quote_id' => '#' . $quoteData->getQuoteIncrementId(),
                'quote_status' => $label,
                'item_info' => $item_info,
                'totals' => $totals];

            $this->helper->sendEmail($template, $email, $template_variables);
            $this->messageManager->addSuccessMessage(__('Po was successfully created.' . $message));
            return $this->_redirect('rfq/po/index');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the PO. Kindly enter the correct data.'));
        }
        return $this->_redirect('rfq/po/index');
    }
}
