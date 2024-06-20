<?php
namespace Matrix\NoncatalogueRfq\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Matrix\NoncatalogueRfq\Helper\Data  as Helper;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

/**
 * Class Success
 */
class SmsNotification implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;

    protected $helper;

    protected $noncatalogRfqFactory;

    protected $storeManager;

    protected $smsnotificationHelper;

    protected $_logger;

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
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Ced\CsTwiliosmsnotification\Helper\Data $smsnotificationHelper,
        \Matrix\NoncatalogueRfq\Logger\Logger $logger,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->noncatalogRfqFactory = $noncatalogRfqFactory;
        $this->smsnotificationHelper =  $smsnotificationHelper;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->_logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        //$this->_logger->info('called SmsNotification');
        $adminMobile =  $this->helper->getConfigValue('noncatalogrfq_configuration/smsnotification/admin_noncatalogrfq_mobile', $this->storeManager->getStore()->getId());
        if ($adminMobile=='') {
            return;
        }

        $noncatalogRfq = $observer['noncatalogRfq'];
        $rfqId = $noncatalogRfq->getData('rfq_id');
        if (!isset($rfqId) || $rfqId<=0) {
            return;
        }

        $arrrfqtype = $this->helper->getRFQuoteTypes();
        $rfqModel = $this->noncatalogRfqFactory->create()->load($rfqId);

        $this->_logger->info('called SmsNotification RFQ DATA', $rfqModel->getData());

        $quoteId = $rfqModel->getRfqId();
        $customerEmail = $rfqModel->getCustomerEmail();
        $rfqType = '';

        $rfqTypeId = (int) $rfqModel->getRfqType();
        if ($rfqTypeId>0 && array_key_exists($rfqTypeId, $arrrfqtype)) {
            $rfqType =  $arrrfqtype[$rfqTypeId];
        }

        $smsVariables = [
        'rfq_type'=> $rfqType,
        'quote_id'=> $quoteId,
        'customer_email'=> $customerEmail
        ];
        //$this->_logger->info('called SmsNotification SMS Vars',$smsVariables);
        $smsContent =   $this->helper->getSMSTemplate($this->helper::SMS_CONFIG_ADMIN_NONCATALOG_RFQ, $smsVariables);
        //$this->_logger->info('called SmsNotification SMS Content'.$smsContent);
        $isSucess = $this->smsnotificationHelper->sendSms($adminMobile, $smsContent);
    }
}
