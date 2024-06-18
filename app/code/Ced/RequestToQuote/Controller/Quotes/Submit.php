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
namespace Ced\RequestToQuote\Controller\Quotes;

use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Message;
use Ced\RequestToQuote\Model\QuoteDetailFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Submit
 * @package Ced\RequestToQuote\Controller\Quotes
 */
class Submit extends Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Quote|QuoteFactory
     */
    protected $_quote;

    /**
     * @var QuoteDetail|QuoteDetailFactory
     */
    protected $_quotedetail;

    /**
     * @var Message
     */
    protected $_message;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RegionFactory
     */
    protected $region;

    /**
     * @var CountryFactory
     */
    protected $country;

    /**
     * @var CollectionFactory
     */
    protected $requestQuoteCollectionFactory;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * Submit constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quote
     * @param QuoteDetailFactory $quotedetail
     * @param Message $message
     * @param Data $helper
     * @param RegionFactory $region
     * @param CountryFactory $country
     * @param CollectionFactory $requestQuoteCollectionFactory
     * @param QuoteStatus $quoteStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        QuoteFactory $quote,
        QuoteDetailFactory $quotedetail,
        Message $message,
        Data $helper,
        RegionFactory $region,
        CountryFactory $country,
        CollectionFactory $requestQuoteCollectionFactory,
        QuoteStatus $quoteStatus,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->session = $customerSession;
        $this->_quote = $quote;
        $this->_quotedetail = $quotedetail;
        $this->_message = $message;
        $this->helper = $helper;
        $this->region = $region;
        $this->country = $country;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        $this->quoteStatus = $quoteStatus;
        parent::__construct($context, $storeManager, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        if ($this->getRequest()->getPost() != null) {
            $data = $this->getRequest()->getParams();
            if(!isset($data['region'])  || !isset($data['region_id'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the state.'));
                return $resultRedirect->setPath('requesttoquote/cart/index');
            }

            if(empty($data['city'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the city.'));
                return $resultRedirect->setPath('requesttoquote/cart/index');
            }

            if(empty($data['street'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct address.'));
                $resultRedirect->setPath('requesttoquote/cart/index');
            }

            if(empty($data['zipcode']) && is_numeric($data['zipcode'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct zipcode.'));
                $resultRedirect->setPath('requesttoquote/cart/index');
            }

            if(empty($data['telephone']) && is_numeric($data['telephone'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct data.'));
                return $resultRedirect->setPath('requesttoquote/cart/index');
            }

            try {
                $item_info = [];
                $totals = [];
                $eventQuoteItems = [];
                $post = $this->getRequest()->getPostValue();
                $totalQty = 0;
                $totalPrice = 0;
                $storeId = $this->_storeManager->getStore()->getId();
                $customerId = $this->session->getCustomerId();
                $customerEmail = $this->session->getCustomer()->getEmail();
                $customerName = $this->session->getCustomer()->getName();
                $quoteItems = $this->requestQuoteCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('store_id', $storeId);
                if (!count($quoteItems)) {
                    $this->messageManager->addErrorMessage(__('You have no items in your quote cart.'));
                    return $resultRedirect->setPath('requesttoquote/cart/index');
                }
                $vendor_id = $quoteItems->getFirstItem()->getVendorId();
                if($post['region']) {
                    $region = $post['region'];
                } else {
                    $region = $this->region->create()->load($post['region_id'])->getName();
                }
                $country_name = $this->country->create()->load($data['country_id'])->getName();
                $quote_collection = $this->_quote->create()
                    ->getCollection();
                if (($quote_collection->getSize()) > 0) {
                    $qo_id =  $quote_collection->getLastItem()->getQuoteId();
                    $qo_id = $qo_id + 1;
                    $qoincId = 'QO' . sprintf("%05d", $qo_id);
                } else {
                    $qoincId = 'QO00001';
                }
                foreach ($quoteItems as $item) {
                    $totalQty += $item->getQuoteQty();
                    $totalPrice += $item->getQuotePrice();
                }
                $quotemodel = $this->_quote->create();
                $quotemodel->setData('customer_id', $customerId);
                $quotemodel->setData('quote_increment_id', $qoincId);
                $quotemodel->setData('vendor_id', $vendor_id);
                $quotemodel->setData('customer_email', $customerEmail);
                $quotemodel->setData('country', $country_name);
                $quotemodel->setData('state', $region);
                $quotemodel->setData('city', $data['city']);
                $quotemodel->setData('pincode', $data['zipcode']);
                $quotemodel->setData('address', implode(',', [$data['street'], $data['area']]));
                $quotemodel->setData('telephone', $data['telephone']);
                $quotemodel->setData('store_id', $this->_storeManager->getStore()->getStoreId());
                $quotemodel->setData('quote_total_qty', $totalQty);
                $quotemodel->setData('quote_total_price', $totalPrice);
                $quotemodel->setData('quote_updated_qty', $totalQty);
                $quotemodel->setData('quote_updated_price', $totalPrice);
                $quotemodel->setData('shipping_amount', 0);
                $quotemodel->setData('shipment_method', "free shipping");
                $quotemodel->setData('status', 'Created');
                $quotemodel->setData('last_updated_by', 'Customer');
                $quotemodel->save();

                foreach ($quoteItems as $item) {
                    $quoteDetails = $this->_quotedetail->create();
                    $prunit_price = $item->getQuotePrice();
                    $prunit_price = sprintf('%0.2f', $prunit_price);
                    $quoteDetails->setData('quote_id', $quotemodel->getQuoteId());
                    $quoteDetails->setData('customer_id', $customerId);
                    if ($customOption = $item->getCustomOption()) {
                        $option = json_decode($customOption, true);
                        $quoteDetails->setData('product_id', $option['simple_product_id']);
                        $quoteDetails->setData('parent_id', $item->getProductId());
                        $quoteDetails->setData('custom_option', $item->getCustomOption());
                    } else {
                        $quoteDetails->setData('product_id', $item->getProductId());
                    }
                    $quoteDetails->setData('vendor_id', $vendor_id);
                    $quoteDetails->setData('product_type', $item->getProductType());
                    $quoteDetails->setData('name', $item->getName());
                    $quoteDetails->setData('sku', $item->getSku());
                    $quoteDetails->setData('store_id', $item->getStoreId());
                    $quoteDetails->setData('product_qty', $item->getQuoteQty());
                    $quoteDetails->setData('price', $prunit_price);
                    $quoteDetails->setData('updated_price', ($item->getQuotePrice() * $item->getQuoteQty()));
                    $quoteDetails->setData('status', '0');
                    $quoteDetails->setData('last_updated_by', 'Customer');
                    $quoteDetails->save();
                    $item_info[$item->getProductId()]['prod_id'] = $item->getProductId();
                    $item_info[$item->getProductId()]['name'] = $item->getName();
                    $item_info[$item->getProductId()]['qty'] = $item->getQuoteQty();
                    $item_info[$item->getProductId()]['sku'] = $item->getSku();
                    $item_info[$item->getProductId()]['price'] = $item->getQuotePrice();
                    $eventQuoteItems[] = $quoteDetails;
                }
                if ($data['message']) {
                    $this->_message->setData('customer_id', $customerId);
                    $this->_message->setData('quote_id', $quotemodel->getQuoteId());
                    $this->_message->setData('vendor_id', $vendor_id);
                    $this->_message->setData('message', $data['message']);
                    $this->_message->setData('sent_by', 'Customer');
                    $this->_message->save();
                }
                $totals['subtotal'] = $totalPrice;
                $totals['grandtotal'] = $totalPrice;
                $template = $this->helper->getConfigValue(Data::QUOTE_CREATE_EMAIL);
                $adminTemplate = $this->helper->getConfigValue(Data::ADMIN_SEND_EMAIL);
                $template_variables = [
                    'quote_id' => '#' . $quotemodel->getQuoteIncrementId(),
                    'quote_status' => $this->quoteStatus->getFrontendOptionText(0),
                    'admin_quote_status' => $this->quoteStatus->getOptionText(0),
                    'item_info' => $item_info,
                    'totals' => $totals,
                    'name' => $customerName
                ];
                $this->helper->sendEmail($template, $customerEmail, $template_variables);
                if ($vendor_id == 0) {
                    $this->helper->sendAdminEmail($adminTemplate, $template_variables, $customerEmail);
                }
                $quoteItems->walk('delete');
                $quoteEventParameter = [
                    'quote' => $quotemodel,
                    'quote_items' => $eventQuoteItems
                ];
                $this->_eventManager->dispatch('ced_request_to_quote_submit_after', $quoteEventParameter);
                $this->messageManager->addSuccessMessage(__('You have successfully submitted your Quote.'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the quote. Kindly enter the correct data.'));
            }
        }
        return $resultRedirect->setPath('requesttoquote/customer/quotes/');
    }
}
