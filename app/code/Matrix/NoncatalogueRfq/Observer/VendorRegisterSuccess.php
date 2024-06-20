<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Matrix\NoncatalogueRfq\Helper\Data  as Helper;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;
use Matrix\NoncatalogueRfq\Model\RfqVendorFactory;

/**
 * Class Success
 */
class VendorRegisterSuccess implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    /** @var Noncatalogquote|NoncatalogRfqFactory
     */
    protected $_noncatalogquote;

    /**
     * @var RfqnonMktVendorFactory|RfqNonMarketVendorFactory
     */
    protected $_rfqnonMktVendorFactory;

    /**
     * @var RfqVenforFactory|RfqVendorFactory
     */
    protected $_rfqVendorFactory;

    /**
     * @var Helper
     */
    protected $helper;

    private $logger;

    /**
     * Success constructor.
     * @param Session $customerSession
     * @param Helper $helper
     * @param NoncatalogRfqFactory $noncatalogRfqFactory
     * @param CustomerCart $cart
     */
    public function __construct(
        Session $customerSession,
        NoncatalogRfqFactory $noncatalogRfqFactory,
        \Psr\Log\LoggerInterface $logger,
        \Matrix\NoncatalogueRfq\Model\RfqNonMarketVendorFactory $rfqnonMktVendorFactory,
        RfqVendorFactory $rfqVendorFactory,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->noncatalogRfqFactory = $noncatalogRfqFactory;
        $this->helper = $helper;
        $this->_rfqnonMktVendorFactory = $rfqnonMktVendorFactory;
        $this->_rfqVendorFactory = $rfqVendorFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        //$this->logger->info("PRITAM customer_register_successfully Obser called");
        //$vendor = $observer->getEvent()->getData('customer');
        $customer = $observer->getEvent()->getDataObject();
        $customerId = $customer->getCustomerId();
        if ($customer && $customer->getData('email')!='') {
            $vendorEmail = $customer->getData('email');
            $nonMktVendorCollection = $this->_rfqnonMktVendorFactory->create()
              ->getCollection()
              ->addFieldToFilter('email', ['eq'=>$vendorEmail])
              ->addFieldToFilter('is_signup', ['eq'=>0]);
            //echo "<br/>Non MKT Bendor  Count=".$nonMktVendorCollection->getSize()."<br />";
            if ($nonMktVendorCollection->getSize()) {
                foreach ($nonMktVendorCollection as $nonMktvendor) {
                    $nonMktvendorEmail = $nonMktvendor->getEmail();
                    if ($nonMktvendorEmail==$vendorEmail) {
                        $supplierData = $nonMktvendor->getEmail();
                        $vendor =  $this->helper->getVendorByCustomerEntityId($customerId);
                        if ($vendor && $vendor->getEntityId()) {
                            //$this->logger->info("PRITAM Loaded vendorfromregisteredcustomer",$vendor->getData());
                            $vendor_id = $vendor->getEntityId();// Vendor Id of current registered customer
                            //$this->logger->info("PRITAM vendor entoty id=".$vendor_id);
                            $rfq_id = $nonMktvendor->getRfqId();
                            $is_emailsend =  $nonMktvendor->getIsEmailsend();
                            $vendor_type = 1;
                            //Create From Non-mkt rfq vendor
                            $rfqMktVendor =  $this->_rfqVendorFactory->create();
                            $rfqMktVendor->setData('rfq_id', $rfq_id);
                            $rfqMktVendor->setData('vendor_type', $vendor_type);
                            $rfqMktVendor->setData('vendor_id', $vendor_id);
                            $rfqMktVendor->setData('is_emailsend', $is_emailsend);

                            //$this->logger->info("PRITAM  New rfqvendordata",$rfqMktVendor->getData());
                            $rfqMktVendor->save();
                            $nonMktvendor->setData('is_signup', 1)->save();
                        } else {
                            //$this->logger->info("PRITAM  vendoraccount not exist for this registration");
                        }
                    }

                }
            }
        }
    }
}
