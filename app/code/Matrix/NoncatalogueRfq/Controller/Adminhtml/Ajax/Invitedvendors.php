<?php
namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\NoncatalogueRfq\Model\ResourceModel\RfqNonMarketVendor\CollectionFactory as rfqNonMarketVendorCollectionFactory;

class Invitedvendors extends \Magento\Backend\App\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $rfqNonMarketVendorCollectionFactory;

    protected $_helper;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        rfqNonMarketVendorCollectionFactory $rfqNonMarketVendorCollectionFactory,
        StoreManagerInterface $storeManager
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->rfqNonMarketVendorCollectionFactory =  $rfqNonMarketVendorCollectionFactory;
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

            $collection  =  $this->getInvitedVendorCollection();
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

    private function getInvitedVendorCollection()
    {
        $rfq_id = (int) $this->getRequest()->getParam('id');
        $collections = $this->rfqNonMarketVendorCollectionFactory->create()
        ->addFieldToFilter('rfq_id', $rfq_id);
        return $collections;
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
