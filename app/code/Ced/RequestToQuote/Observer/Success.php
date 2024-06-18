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

use Ced\RequestToQuote\Helper\Data as Helper;
use Ced\RequestToQuote\Model\PoDetailFactory;
use Ced\RequestToQuote\Model\PoFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\PoDetail\CollectionFactory as PoDetailCollectionFactory;
use Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Success
 * @package Ced\RequestToQuote\Observer
 */
class Success implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PoDetailFactory
     */
    protected $poDetailFactory;

    /**
     * @var PoDetailCollectionFactory
     */
    protected $poDetailCollectionFactory;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Success constructor.
     * @param Session $customerSession
     * @param PoDetailFactory $poDetailFactory
     * @param PoDetailCollectionFactory $poDetailCollectionFactory
     * @param QuoteStatus $quoteStatus
     * @param PoFactory $poFactory
     * @param Helper $helper
     * @param QuoteFactory $quoteFactory
     * @param PoCollectionFactory $poCollectionFactory
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param CustomerCart $cart
     */
    public function __construct(
        Session $customerSession,
        PoDetailFactory $poDetailFactory,
        PoDetailCollectionFactory $poDetailCollectionFactory,
        QuoteStatus $quoteStatus,
        PoFactory $poFactory,
        Helper $helper,
        QuoteFactory $quoteFactory,
        PoCollectionFactory $poCollectionFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        CustomerCart $cart
    ) {
        $this->session = $customerSession;
        $this->poDetailFactory = $poDetailFactory;
        $this->poDetailCollectionFactory = $poDetailCollectionFactory;
        $this->quoteStatus = $quoteStatus;
        $this->poFactory = $poFactory;
        $this->helper = $helper;
        $this->quoteFactory = $quoteFactory;
        $this->poCollectionFactory = $poCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order_id = $observer->getEvent()->getData('order_ids');
        $orderid = $order_id[0];
        $products = [];
        $poIncid = $this->session->getData('po_id');
        if(isset($poIncid)) {
            $poCollection = $this->poDetailCollectionFactory->create()->addFieldToFilter('po_id', $poIncid)->getData();
            foreach ($poCollection as $key => $value) {
                $po_id=$value['po_id'];
                $id = $value['id'];
                if(isset($po_id)) {
                    $poload = $this->poDetailFactory->create()->load($id);
                    $poload->setData('status', '4');
                    $poload->setData('order_id', $orderid);
                    $poload->save();
                }
            }
            try {
                if(isset($poIncid)) {
                    $podata = $this->poFactory->create()->load($poIncid, 'po_increment_id');
                    $quoteId = $podata->getQuoteId();
                    $podata->setStatus(3);
                    $podata->setOrderId($orderid);
                    $podata->save();
                    $quoteData = $this->quoteFactory->create()->load($quoteId);
                    $po_datas = $this->poCollectionFactory->create()->addFieldToFilter('quote_id', $quoteId);
                    $quote_status = true;
                    $count = 0;
                    foreach($po_datas as $po_data) {
                        if($po_data->getStatus() != 3) {
                            $quote_status = false;
                            break;
                        } else {
                            $count++;
                        }
                    }
                    if($quoteData->getRemainingQty() === '0' && $quote_status) {
                        if ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED) {
                            $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE);
                            $quoteData->save();
                        } else {
                            $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE);
                            $quoteData->save();
                        }
                        $email = $quoteData->getCustomerEmail();
                        $customerName = '';
                        $customer = $this->session->getCustomer();
                        if ($customer && $customer->getId()) {
                            $customerName = $customer->getName();
                        }
                        $adminTemplate = $this->helper->getConfigValue(Helper::ADMIN_QUOTE_COMPLETE_EMAIL);
                        $template_variables = [
                            'quote_id' => '#' . $quoteData->getQuoteIncrementId(),
                            'quote_status' => $this->quoteStatus->getOptionText($quoteData->getStatus()),
                            'name' => $customerName
                        ];
                        $this->helper->sendAdminEmail($adminTemplate, $template_variables, $email);

                    } else {
                        if ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED && !$quote_status) {
                            if($count < count($po_datas)) {
                                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_COMPLETE);
                                $quoteData->save();
                            } else {
                                $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE);
                                $quoteData->save();
                            }
                        }
                        $email = $quoteData->getCustomerEmail();
                        $customerName = '';
                        $customer = $this->session->getCustomer();
                        if ($customer && $customer->getId()) {
                            $customerName = $customer->getName();
                        }
                        $adminTemplate = $this->helper->getConfigValue(Helper::ADMIN_QUOTE_UPDATE_EMAIL);
                        $template_variables = [
                            'quote_id' => '#' . $quoteData->getQuoteIncrementId(),
                            'quote_status' => $this->quoteStatus->getOptionText($quoteData->getStatus()),
                            'name' => $customerName
                        ];
                        $this->helper->sendAdminEmail($adminTemplate, $template_variables, $email);
                    }
                }
            } catch(\Exception $e) {
            }
        }
    }
}
