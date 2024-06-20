<?php
namespace Matrix\NoncatalogueRfq\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class VendorsbyIndustry extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $vendorFactory;

    protected $_scopeConfig;

    protected $_categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->vendorFactory = $vendorFactory;
        $this->storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
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
            if (!isset($postparams['catid'])) {
                return $this->getErrorResult();
            }
            $isVendorExist = false;
            $result['message'] =  __('No Vendor exist for selected industry.');
            $catid =  $postparams['catid'];
            $industryCatdId =  $this->getlevelTwoCategoryId($catid);
            $collection = $this->vendorFactory->create()
            ->getCollection()
            ->addAttributeToSelect('public_name')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('group')
            ->addAttributeToSelect('status')
            ->addAttributeToFilter('status', ['eq'=>'approved'])
            ->addFieldtoFilter('industry', $industryCatdId);
            if ($collection->getSize()>0) {
                $isVendorExist = true;
                $arrOptions = [];
                foreach ($collection as $item) {
                    $arrOptions[] =  [
                    "value" => $item->getData('entity_id'),
                    "label" => $item->getData('public_name')
                    ];
                }

                $result['message'] = $collection->getSize() . __('  Vendor found for selected industry.');
            }
            $result['vendorlist'] =  $arrOptions;
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

    private function getlevelTwoCategoryId($categoryId)
    {
        $category = $this->_categoryFactory->create()->load($categoryId);
        $currentCategoryLevel = $category->getLevel();
        $parentId = $category->getParentId();
        $levelTwocategoryId = $category->getId();
        while ($currentCategoryLevel!=2) {
            $category = $this->_categoryFactory->create()->load($parentId);
            $parentId = $category->getParentId();
            $currentCategoryLevel = $category->getLevel();
            $levelTwocategoryId = $category->getId();
        }
        return $levelTwocategoryId;
    }
}
