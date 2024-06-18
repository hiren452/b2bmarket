<?php

namespace Ced\RequestToQuote\Block\Customer;

use Ced\RequestToQuote\Model\Po;
use Ced\RequestToQuote\Model\PoDetail;
use Ced\RequestToQuote\Model\Quote;
use Ced\RequestToQuote\Model\Source\PoStatus;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManager;

class EditPo extends Template
{
    /**
     * @var null
     */
    protected $currentPo = null;
    /**
     * @var Quote
     */
    protected $_quote;
    /**
     * @var Po
     */
    protected $_po;
    /**
     * @var PoDetail
     */
    protected $_podetail;
    /**
     * @var StoreManager
     */
    protected $storeManager;
    /**
     * @var Customer
     */
    protected $_customerData;

    /**
     * @var CurrencyFactory
     */
    protected $currency;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PoStatus
     */
    protected $poStatus;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @param Context $context
     * @param Quote $quote
     * @param Po $po
     * @param PoDetail $podetail
     * @param StoreManager $storeManager
     * @param Customer $customerData
     * @param CurrencyFactory $currencyFactory
     * @param PoStatus $poStatus
     * @param Registry $registry
     * @param CustomerCart $cart
     */
    public function __construct(
        Context $context,
        Quote $quote,
        Po $po,
        PoDetail $podetail,
        StoreManager $storeManager,
        Customer $customerData,
        CurrencyFactory $currencyFactory,
        PoStatus $poStatus,
        Registry $registry,
        CustomerCart $cart,
        Data $priceingHelper
    ) {
        $this->_quote = $quote;
        $this->_po = $po;
        $this->_podetail = $podetail;
        $this->storeManager = $storeManager;
        $this->_customerData = $customerData;
        $this->currency = $currencyFactory;
        $this->registry = $registry;
        $this->poStatus = $poStatus;
        $this->cart = $cart;
        $this->priceingHelper = $priceingHelper;
        parent::__construct($context);
    }

    /**
     * @return false
     */
    public function getQuotePoId()
    {
        $allItems = $this->cart->getQuote()->getAllItems();
        if ($allItems) {
            foreach ($allItems as $item) {
                if ($poId = $item->getCedPoId()) {
                    return $poId;
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isCurrentQuoteHasItems()
    {
        $allItems = $this->cart->getQuote()->getAllItems();
        if (count($allItems)) {
            return true;
        }
        return false;
    }

    /**
     * @return Po|mixed|null
     */
    public function getPoInfo()
    {
        if (!$this->currentPo) {
            if ($currentPo = $this->registry->registry('current_po')) {
                $this->currentPo = $currentPo;
            } else {
                $this->currentPo = $this->_po->load($this->getRequest()->getParam('poId'));
            }
        }
        return $this->currentPo;
    }

    /**
     * @return $this|EditPo
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()
                ->createBlock('Magento\Theme\Block\Html\Pager', 'my.custom.pager')
                ->setLimit(5)
                ->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->getTitle()->set("#" . $this->_po->load($this->getRequest()->getParam('poId'))->getPoIncrementId());
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl(
            'requesttoquote/customer/savequotes',
            ['quoteId'=> $this->getRequest()->getParam('poId')]
        );
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('carttoquote/myquote/index');
    }

    /**
     * @param $id
     * @return string
     */
    public function getVendor($id)
    {
        return "Admin Product";
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        $code =  $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        return $this->currency->create()->create()->load($code);
    }

    /**
     * @return mixed|null
     */
    public function getPoStatus()
    {
        $po_status = $this->getPoInfo()->getStatus();
        return $this->poStatus->getOptionText($po_status);
    }

    /**
     * @param $po_increment_id
     * @param $quote_id
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     */
    public function getPoData($po_increment_id, $quote_id)
    {
        return $this->_podetail->getCollection()
            ->addFieldToFilter('quote_id', $quote_id)
            ->addFieldToFilter('po_id', $po_increment_id);
    }

    /**
     * @param $customer_id
     * @return Customer
     */
    public function getCustomer($customer_id)
    {
        return $this->_customerData->load($customer_id);
    }

    /**
     * @return array
     */
    public function getCustomerAddress()
    {
        $address = [];
        $quote_id = $this->getPoInfo()->getData('quote_id');
        $addressdata = $this->_quote->load($quote_id);
        $address['country'] = $addressdata->getCountry();
        $address['state'] = $addressdata->getState();
        $address['city'] = $addressdata->getCity();
        $address['pincode'] = $addressdata->getPincode();
        $address['street'] = $addressdata->getAddress();
        $address['telephone'] = $addressdata->getTelephone();
        return $address;
    }
}
