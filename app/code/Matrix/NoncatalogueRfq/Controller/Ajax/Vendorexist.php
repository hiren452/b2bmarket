<?php
namespace Matrix\NoncatalogueRfq\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class Vendorexist extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $vendorFactory;

    protected $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/stockists_index_index.xml
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {

        try {
            $postparams = $this->getRequest()->getParams();
            if (!isset($postparams['email'])) {
                return $this->getErrorResult();
            }
            $isVendorExist = false;
            $result['message'] =  __('Vendor Email not exist.');
            $email =  $postparams['email'];
            $collection = $this->vendorFactory->create()->getCollection()
            ->addFieldtoFilter('email', $email);
            if ($collection->getSize()>0) {
                $isVendorExist = true;
                $result['message'] =  __('Vendor with this email already exist in ') . $this->getStoreName();
            }
            $result['vendorexist'] =  $isVendorExist;
            $result['status'] =  true;
            return  $this->resultJsonFactory->create()->setData($result);
        } catch (\Exception $exception) {
            return $this->getErrorResult($exception->getMessage());
        }
    }

    private function getErrorResult($message)
    {
        $result = [];
        $result['message'] =  $message;
        $result['vendorexist'] =  true;
        $result['status'] =  false;
        return  $this->resultJsonFactory->create()->setData($result);
    }

    public function getStoreName()
    {
        return $this->_scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
