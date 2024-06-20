<?php
namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class Vendors extends \Magento\Backend\App\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $_vendorFactory;

    protected $_helper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        StoreManagerInterface $storeManager
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->_vendorFactory = $vendorFactory;
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

            $collection  =  $this->getRfqVendorCollection();
            $data = [];
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $data[] = $item->getData();
                }
            }

            if (count($data)) {
                $result['data'] =  $data;
            } else {
                $result['data'] =  null;
            }
            $result['message'] =  'success';
            $result['status'] =  true;
            return  $this->resultJsonFactory->create()->setData($result);
        } catch (\Exception $exception) {
            return $this->getErrorResult($exception->getMessage());
        }
    }

    private function getRfqVendorCollection()
    {
        $rfq_id = (int) $this->getRequest()->getParam('id');
        $collection = $this->_vendorFactory->create()->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', ['eq'=>'approved']);
        $noncatrfq_vendor_tbl = 'matrix_noncatalog_rfq_vendor';
        $collection->getSelect()->joinInner($noncatrfq_vendor_tbl, 'e.entity_id = ' . $noncatrfq_vendor_tbl . '.vendor_id AND ' . $noncatrfq_vendor_tbl . '.rfq_id = ' . $rfq_id, ['is_emailsend ']);
        return $collection;
    }

    private function getErrorResult($message)
    {
        $result = [];
        $result['options'] =  null;
        $result['message'] =  $message;
        $result['status'] =  false;
        return  $this->resultJsonFactory->create()->setData($result);
    }
}
