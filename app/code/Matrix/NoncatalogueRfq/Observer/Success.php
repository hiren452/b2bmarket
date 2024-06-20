<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Checkout\Model\Cart as CustomerCart;
//use Ced\RequestToQuote\Model\PoDetailFactory;
use Magento\Customer\Model\Session;

///use Ced\RequestToQuote\Model\ResourceModel\PoDetail\CollectionFactory as PoDetailCollectionFactory;
use Magento\Framework\Event\ObserverInterface;

use Matrix\NoncatalogueRfq\Helper\Data  as Helper;

//use Ced\RequestToQuote\Model\PoFactory;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

//use Ced\RequestToQuote\Helper\Data as Helper;
use Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq\CollectionFactory as QuoteCollectionFactory;

//use Ced\RequestToQuote\Model\QuoteFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo\CollectionFactory as PoCollectionFactory;

//use Ced\RequestToQuote\Model\ResourceModel\Po\CollectionFactory as PoCollectionFactory;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPodetail\CollectionFactory as PoDetailCollectionFactory;

//use Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Matrix\NoncatalogueRfq\Model\RfqPodetailFactory;

use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

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

    private $logger;

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
        RfqPodetailFactory $poDetailFactory,
        PoDetailCollectionFactory $poDetailCollectionFactory,
        QuoteStatus $quoteStatus,
        RfqPoFactory $poFactory,
        Helper $helper,
        NoncatalogRfqFactory $quoteFactory,
        PoCollectionFactory $poCollectionFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
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
        $this->logger = $logger;
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
        $poIncid = $this->session->getData('matrix_po_id');
        if (isset($poIncid)) {
            $poCollection = $this->poDetailCollectionFactory->create()->addFieldToFilter('po_id', $poIncid)->getData();
            foreach ($poCollection as $key => $value) {
                $po_id=$value['po_id'];
                $id = $value['id'];
                if (isset($po_id)) {
                    $poload = $this->poDetailFactory->create()->load($id);
                    $poload->setData('status', '4');
                    $poload->setData('order_id', $orderid);
                    $poload->save();
                }
            }
            try {
                if (isset($poIncid)) {
                    $podata = $this->poFactory->create()->load($poIncid, 'po_increment_id');
                    $quoteId = $podata->getRfqId();
                    $podata->setStatus(3);
                    $podata->setOrderId($orderid);
                    $podata->save();
                    $quoteData = $this->quoteFactory->create()->load($quoteId);
                    $po_datas = $this->poCollectionFactory->create()->addFieldToFilter('rfq_id', $quoteId);
                    $quote_status = true;
                    foreach ($po_datas as $po_data) {
                        if ($po_data->getStatus() != 3) {
                            $quote_status = false;
                            break;
                        }
                    }
                    //if($quoteData->getRemainingQty() === '0' && $quote_status){
                    if ($quote_status) {
                        if ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED) {
                            $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE);
                            $quoteData->save();
                        }
                        $email = $quoteData->getCustomerEmail();
                        $customerName = '';
                        $customer = $this->session->getCustomer();
                        if ($customer && $customer->getId()) {
                            $customerName = $customer->getName();
                        }
                        $template = 'quote_complete_email_template';
                        $template_variables = [
                            'quote_id' => '#' . $quoteData->getQuoteIncrementId(),
                            'quote_status' => $this->quoteStatus->getOptionText($quoteData->getStatus()),
                            'name' => $customerName
                        ];
                        //$this->logger->info("Quote Suces Email Template =".$template);
                        //$this->logger->info("Quote Suces Email template_variables =",$template_variables);
                        //$this->logger->info("Quote Suces Email email =",$email);
                        $this->helper->sendAdminEmail($template, $template_variables, $email);

                    } /*else {
                        if ($quoteData->getStatus() == \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED) {
                            $quoteData->setStatus(\Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE);
                            $quoteData->save();
                        }
                        $email = $quoteData->getCustomerEmail();
                        $customerName = '';
                        $customer = $this->session->getCustomer();
                        if ($customer && $customer->getId())
                        {
                            $customerName = $customer->getName();
                        }
                        $template = 'quote_update_email_template';
                        $template_variables = [
                            'quote_id' => '#'.$quoteData->getQuoteIncrementId(),
                            'quote_status' => $this->quoteStatus->getOptionText($quoteData->getStatus()),
                            'name' => $customerName
                        ];
                        $this->helper->sendAdminEmail($template, $template_variables, $email);
                    }*/
                }
            } catch (\Exception $e) {
            }
        }
    }
}
