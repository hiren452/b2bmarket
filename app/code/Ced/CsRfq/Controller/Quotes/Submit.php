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
namespace Ced\CsRfq\Controller\Quotes;

use Ced\CsMarketplace\Model\ResourceModel\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\RequestToQuote\Helper\Data;
use Ced\RequestToQuote\Model\Message;
use Ced\RequestToQuote\Model\QuoteDetailFactory;
use Ced\RequestToQuote\Model\QuoteFactory;
use Ced\RequestToQuote\Model\ResourceModel\RequestQuote\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var Vendor
     */
    protected $vendorResource;

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
     * @param VendorFactory $vendorFactory
     * @param Vendor $vendorResource
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
        VendorFactory $vendorFactory,
        Vendor $vendorResource,
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
        $this->vendorFactory = $vendorFactory;
        $this->vendorResource = $vendorResource;
        $this->requestQuoteCollectionFactory = $requestQuoteCollectionFactory;
        parent::__construct(
            $context,
            $storeManager,
            $data
        );
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            return $this->_redirect('customer/account/login');
        }
        if ($this->getRequest()->getPost() != null) {
            $data = $this->getRequest()->getParams();
            if(!isset($data['region'])  || !isset($data['region_id'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the state.'));
                return  $this->_redirect('requesttoquote/cart/index');
            }

            if(empty($data['city'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the city.'));
                return $this->_redirect('requesttoquote/cart/index');
            }

            if(empty($data['street'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct address.'));
                return $this->_redirect('requesttoquote/cart/index');
            }

            if(empty($data['zipcode']) && is_numeric($data['zipcode'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct zipcode.'));
                return $this->_redirect('requesttoquote/cart/index');
            }

            if(empty($data['telephone']) && is_numeric($data['telephone'])) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the quote. Kindly enter the correct data.'));
                return $this->_redirect('requesttoquote/cart/index');
            }

            try {
                $item_info = [];
                $totals = [];
                $post = $this->getRequest()->getPostValue();
                $vendor_id = 0;
                $totalQty = 0;
                $totalPrice = 0;
                $storeId = $this->_storeManager->getStore()->getId();

                $quoteItemsGroup = $this->requestQuoteCollectionFactory->create()
                ->addFieldToFilter('customer_id', $this->session->getCustomerId())
                ->addFieldToFilter('store_id', $storeId);
                $quoteItemsGroup->getSelect()->group('vendor_id');

                if (!count($quoteItemsGroup)) {
                    $this->messageManager->addErrorMessage(__('You have no items in your quote cart.'));
                    return $this->_redirect('requesttoquote/cart/index');
                }
                if($post['region']) {
                    $region = $post['region'];
                } else {
                    $region = $this->region->create()->load($post['region_id'])->getName();
                }
                $country_name = $this->country->create()->load($data['country_id'])->getName();

                $template = $this->helper->getConfigValue(Data::QUOTE_CREATE_EMAIL);
                $adminTemplate = $this->helper->getConfigValue(Data::ADMIN_SEND_EMAIL);
                foreach($quoteItemsGroup as $quoteData) {

                    $quote_collection = $this->_quote->create()
                    ->getCollection();
                    if(sizeof($quote_collection) > 0) {
                        $qo_id =  $quote_collection->getLastItem()->getQuoteId();
                        $qo_id = $qo_id + 1;
                        $qoincId = 'QO' . sprintf("%05d", $qo_id);

                    } else {
                        $qoincId = 'QO00001';
                    }
                    $vendorModel = $this->vendorFactory->create();
                    $this->vendorResource->load($vendorModel, $quoteData['vendor_id']);
                    $vendorEmail = $vendorModel->getEmail();
                    $quoteItemsVendorWise = $this->requestQuoteCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $this->session->getCustomerId())
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('vendor_id', $quoteData['vendor_id']);
                    foreach ($quoteItemsVendorWise as $item) {
                        $totalQty += $item->getQuoteQty();
                        $totalPrice += $item->getQuotePrice();
                    }

                    $quotemodel = $this->_quote->create();
                    $quotemodel->setData('customer_id', $data['customerId']);
                    $quotemodel->setData('quote_increment_id', $qoincId);
                    $quotemodel->setData('vendor_id', $quoteData['vendor_id']);
                    $quotemodel->setData('customer_email', $data['customeremail']);
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

                    $template_variables = ['quote_id' => '#' . $quotemodel->getQuoteIncrementId(),
                                            'quote_status' => __('Pending'),
                                            'item_info' => $item_info,
                                            'totals' => $totals];

                    $quoteItems = $this->requestQuoteCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $this->session->getCustomerId())
                    ->addFieldToFilter('store_id', $storeId)->addFieldToFilter('vendor_id', $quoteData['vendor_id']);

                    foreach ($quoteItems as $item) {
                        $quoteDetails = $this->_quotedetail->create();
                        $prunit_price = $item->getQuotePrice();
                        $prunit_price = sprintf('%0.2f', $prunit_price);
                        $quoteDetails->setData('quote_id', $quotemodel->getQuoteId());
                        $quoteDetails->setData('customer_id', $data['customerId']);
                        if ($customOption = $item->getCustomOption()) {
                            $option = json_decode($customOption, true);
                            $quoteDetails->setData('product_id', $option['simple_product_id']);
                            $quoteDetails->setData('parent_id', $item->getProductId());
                            $quoteDetails->setData('custom_option', $item->getCustomOption());
                        } else {
                            $quoteDetails->setData('product_id', $item->getProductId());
                        }
                        $quoteDetails->setData('vendor_id', $item->getVendorId());
                        $quoteDetails->setData('product_type', $item->getProductType());
                        $quoteDetails->setData('name', $item->getName());
                        $quoteDetails->setData('sku', $item->getSku());
                        $quoteDetails->setData('store_id', $item->getStoreId());
                        $quoteDetails->setData('product_qty', $item->getQuoteQty());
                        $quoteDetails->setData('price', $prunit_price);
                        /*                    $quoteDetails->setData('quote_updated_qty', $item->getQuoteQty());*/
                        $quoteDetails->setData('updated_price', ($item->getQuotePrice() * $item->getQuoteQty()));
                        /*                    $quoteDetails->setData('unit_price', $prunit_price);*/
                        $quoteDetails->setData('status', '0');
                        $quoteDetails->setData('last_updated_by', 'Customer');
                        $quoteDetails->save();
                        $item_info[$item->getProductId()]['prod_id'] = $item->getProductId();
                        $item_info[$item->getProductId()]['name'] = $item->getName();
                        $item_info[$item->getProductId()]['qty'] = $item->getQuoteQty();
                        $item_info[$item->getProductId()]['sku'] = $item->getSku();
                        $item_info[$item->getProductId()]['price'] = $item->getQuotePrice();
                    }

                    if ($data['message']) {
                        $this->_message->setData('customer_id', $this->session->getCustomerId());
                        $this->_message->setData('quote_id', $quotemodel->getQuoteId());
                        $this->_message->setData('vendor_id', $quoteData['vendor_id']);
                        $this->_message->setData('message', $data['message']);
                        $this->_message->setData('sent_by', 'Customer');
                        $this->_message->save();
                    }
                    $quoteItems->walk('delete');
                    $vendor_template_variables = ['vendor_email' => $vendorEmail,
                                                    'customer_email' => $data['customeremail']];
                    $this->helper->sendVendorEmail($adminTemplate, $template_variables, $vendor_template_variables);
                }
                $totals['subtotal'] = $totalPrice;
                $totals['grandtotal'] = $totalPrice;
                $this->helper->sendEmail($template, $data['customeremail'], $template_variables);
                //$this->helper->sendAdminEmail($adminTemplate,$data['customeremail'],$template_variables);
                $this->helper->sendAdminEmail($adminTemplate, $template_variables, $data['customeremail']);
                $this->messageManager->addSuccessMessage(__('Quote was saved successfully'));
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the quote. Kindly enter the correct data.'));
            }
        }
        return $this->_redirect('requesttoquote/customer/quotes/');
    }
}
