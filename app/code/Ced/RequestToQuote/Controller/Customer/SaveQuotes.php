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

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Message;
use Ced\RequestToQuote\Model\Quote;
use Ced\RequestToQuote\Model\ResourceModel\QuoteDetail\CollectionFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class SaveQuotes extends Action
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var CollectionFactory
     */
    protected $quoteDetail;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * SaveQuotes constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param Quote $quote
     * @param CollectionFactory $quoteDetail
     * @param Message $message
     * @param Data $helper
     * @param QuoteStatus $quoteStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Quote $quote,
        CollectionFactory $quoteDetail,
        Message $message,
        Data $helper,
        QuoteStatus $quoteStatus,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->session = $customerSession;
        $this->quote = $quote;
        $this->quoteDetail = $quoteDetail;
        $this->message = $message;
        $this->quoteStatus = $quoteStatus;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        if (!$this->getRequest()->getParam('quote_id')) {
            $this->messageManager->addErrorMessage(__('Something wen\'t wrong please try again.'));
            $resultRedirect->setPath('requesttoquote/customer/quotes');
            return $resultRedirect;
        }

        $quote = $this->quote->load($this->getRequest()->getParam('quote_id'));
        $customer_id = $this->session->getCustomer()->getId();
        if ($quote && $quote->getId()) {
            if ($customer_id != $quote->getCustomerId()) {
                $this->messageManager->addErrorMessage(__('This quote does not belongs to you.'));
                $resultRedirect->setPath('requesttoquote/customer/quotes');
                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
            $resultRedirect->setPath('requesttoquote/customer/quotes');
        }
        try {
            $postdata = $this->getRequest()->getParams();
            if ($this->getRequest()->isPost()) {
                if ($this->getRequest()->getPost() != null) {
                    $item_info = [];
                    $totals = [];
                    $quoteDescription = $this->quoteDetail->create()->addFieldToFilter('quote_id', $postdata['quote_id']);
                    $customerId = $quote->getCustomerId();
                    $customerName = '';
                    $customer = $this->session->getCustomer();
                    if ($customer && $customer->getId()) {
                        $customerName = $customer->getName();
                    }
                    if ($customer_id == $customerId) {
                        $quote_total_qty = 0;
                        $quote_total_price = 0;
                        foreach ($quoteDescription as $value) {
                            $product_id = $value->getProductId();
                            if ($this->getRequest()->getParam('update') && isset($postdata['item'])) {
                                if ($postdata['item'][$value->getId()]['price'] > 0) {
                                    $updateduprice = $postdata['item'][$value->getId()]['price'];
                                    $value->setData('price', $updateduprice);
                                } else {
                                    $updateduprice = $value->getUnitPrice();
                                }

                                if ($postdata['item'][$value->getId()]['qty'] > 0) {
                                    $updatedqty = $postdata['item'][$value->getId()]['qty'];
                                    $value->setData('product_qty', $updatedqty);
                                } else {
                                    $updatedqty = $value->getQuoteUpdatedQty();
                                }
                            } elseif ($this->getRequest()->getParam('approve')) {
                                $updateduprice = $value->getUnitPrice();
                                $updatedqty = $value->getQuoteUpdatedQty();
                                $value->setData('price', $updateduprice);
                                $value->setData('product_qty', $updatedqty);
                            } else {
                                $updateduprice = $value->getUnitPrice();
                                $updatedqty = $value->getQuoteUpdatedQty();
                            }
                            $quote_total_qty += $updatedqty;
                            $updatedprice = ($updatedqty * $updateduprice);
                            $quote_total_price += $updatedprice;
                            $value->setData('updated_price', $updatedprice);
                            $value->save();
                            $item_info[$product_id]['prod_id'] = $product_id;
                            $item_info[$product_id]['name'] = $value->getName();
                            $item_info[$product_id]['sku'] = $value->getSku();
                            $item_info[$product_id]['qty'] = $updatedqty;
                            $item_info[$product_id]['price'] = $updatedprice;
                        }
                        $quote->setQuoteUpdatedQty($quote_total_qty)
                               ->setQuoteUpdatedPrice($quote_total_price);
                        if ($this->getRequest()->getParam('approve')) {
                            $quote->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED);
                        } elseif ($this->getRequest()->getParam('update')) {
                            $quote->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PENDING);
                        }
                        $quote->save();
                        if ($this->getRequest()->getParam('send') && $postdata['message']) {
                            $this->message->setData('customer_id', $customer_id);
                            $this->message->setData('quote_id', $postdata['quote_id']);
                            $this->message->setData('vendor_id', $quote->getVendorId());
                            $this->message->setData('message', $postdata['message']);
                            $this->message->setData('sent_by', 'Customer');
                            $this->message->save();
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update your quotes only.'));
                        $resultRedirect->setPath('customer/account/index');
                        return $resultRedirect;
                    }
                }
            }
            $status = $quote->getStatus();
            $label = $this->quoteStatus->getOptionText($status);
            $totals['subtotal'] = $quote->getQuoteUpdatedPrice();
            $totals['shipping'] = $quote->getShippingAmount();
            $totals['grandtotal'] = $totals['subtotal'] + $totals['shipping'];
            $email = $quote->getCustomerEmail();
            $template = $this->helper->getConfigValue(Data::QUOTE_UPDATE_EMAIL);
            $adminTemplate = $this->helper->getConfigValue(Data::ADMIN_QUOTE_UPDATE_EMAIL);
            $template_variables = [
                'quote_id' => '#' . $quote->getQuoteIncrementId(),
                'quote_status' => $label,
                'item_info' => $item_info,
                'totals' => $totals,
                'name' => $customerName
            ];
            $this->helper->sendAdminEmail($adminTemplate, $template_variables, $email);
            if ($this->getRequest()->getParam('update')) {
                $this->messageManager->addSuccessMessage(__('Quote has been updated successfully.'));
            } elseif ($this->getRequest()->getParam('approve')) {
                $this->messageManager->addSuccessMessage(__('Quote has been successfully approved.'));
            } else {
                $this->messageManager->addSuccessMessage(__('Message has been send successfully.'));
            }
            $resultRedirect->setPath(
                'requesttoquote/customer/editquote',
                ['quoteId'=> $postdata['quote_id']]
            );
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something wen\'t wrong please try again.'));
            $this->logger->critical('Error message', ['exception' => $e]);
            $resultRedirect->setPath(
                'requesttoquote/customer/editquote',
                ['quoteId'=> $postdata['quote_id']]
            );
            return $resultRedirect;
        }
    }
}
