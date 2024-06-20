<?php

namespace Matrix\NoncatalogueRfq\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $categroyuomCollectionFactory;

    protected $_helper;

    /**
     * @var UomoptionsFactory
     */
    protected $_uomoptionsFactory;

    protected $_categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Matrix\NoncatalogueRfq\Model\ResourceModel\CategroyUom\CollectionFactory  $categroyuomCollectionFactory,
        \Matrix\NoncatalogueRfq\Model\UomOptionsFactory $uomoptionsFactory,
        \Matrix\NoncatalogueRfq\Helper\Data $helper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categroyuomCollectionFactory = $categroyuomCollectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_uomoptionsFactory =  $uomoptionsFactory;
        $this->_helper = $helper;
        $this->storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
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
            if (!isset($postparams['cat_ids'])) {
                return $this->getErrorResult();
            }
            $cat_ids  =  $postparams['cat_ids'];
            $catUomcollection = $this->categroyuomCollectionFactory->create();
            $catUomcollection->addFieldToSelect('uom_options');
            $catUomcollection->addFieldToFilter('category_id', ['in'=>$cat_ids]);
            //echo $catUomcollection->getSelect();
            $optionIdsString='';
            if (count($catUomcollection)<=0) {
                $levelTwoCatId = $this->getlevelTwoCategoryId($cat_ids);

                if ($levelTwoCatId != $cat_ids) {
                    $catUomcollection = $this->categroyuomCollectionFactory->create();
                    $catUomcollection->addFieldToSelect('uom_options');
                    $catUomcollection->addFieldToFilter('category_id', ['in'=>$levelTwoCatId]);

                }
            }

            foreach ($catUomcollection as $item) {

                //$arrOptionIds[] = explode(",", $item->getData('uom_options'));
                $optionIdsString.=$item->getData('uom_options') . ",";
            }

            $arrOptionIds = explode(",", $optionIdsString);
            $arrOptionIds =  array_unique($arrOptionIds);
            $arrOptionIds[] =  $this->_helper::UOM_OPTION_OTHER_ID;

            $options = $this->_uomoptionsFactory->create()->getUomOptions();
            $optionFinal = [];
            foreach ($options as $key => $option) {
                if (in_array($option['value'], $arrOptionIds) && $option['value']>0) {
                    $optionFinal[] = $option;
                }

            }

            $result['options'] =  $optionFinal;
            $result['message'] =  'success';
            $result['status'] =  true;
            return  $this->resultJsonFactory->create()->setData($result);
        } catch (\Exception $exception) {
            return $this->getErrorResult($exception->getMessage());
        }
    }

    private function getErrorResult($message)
    {
        $result = [];
        $result['options'] =  null;
        $result['message'] =  $message;
        $result['status'] =  false;
        return  $this->resultJsonFactory->create()->setData($result);
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
