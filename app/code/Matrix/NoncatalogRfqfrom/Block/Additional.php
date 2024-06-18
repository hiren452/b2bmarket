<?php
namespace Matrix\NoncatalogRfqfrom\Block;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;
//use Matrix\RfqEntity\Model\RfqEntity;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Additional extends \Magento\Framework\View\Element\Template
{

    const ENTITY_TYPE_ID = 10;

    protected $_attributeCollection;

    protected $_eavEntity;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;

    protected $_coreRegistry = null;

    /**
    * @var rfqEntity
    */
    //protected $rfqEntity;

    /**
    * @var RfqEntityFactory
    */
    protected $rfqentityFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    //protected $_attributeFactory;

    protected $matrixattributeFactory;

    protected $entityAttribute;

    /**
     * Additional constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param CustomerFactory $customer
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Matrix\RfqEntity\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        //RfqEntity $rfqEntity,
        RfqEntityFactory $rfqentityFactory,
        \Magento\Eav\Model\Entity $eavEntity,
        CustomerFactory $customer,
        Registry $registry,
        //\Magento\Customer\Model\AttributeFactory $attributeFactory,
        Session $session,
        \Matrix\NoncatalogRfqfrom\Model\AttributeFactory $matrixattributeFactory,
        \Magento\Eav\Model\Entity\AttributeFactory $entityAttribute,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->_attributeCollection = $attributeCollection;
        $this->_eavEntity = $eavEntity;
        $this->_coreRegistry = $registry;
        //$this->_attributeFactory = $attributeFactory;
        //$this->rfqEntity = $rfqEntity;
        $this->rfqentityFactory = $rfqentityFactory;
        $this->_customer = $customer;
        $this->_session = $session;
        $this->storeManager = $context->getStoreManager();
        $this->matrixattributeFactory = $matrixattributeFactory;
        $this->entityAttribute = $entityAttribute;
    }

    public function rfQattrCollection()
    {
        $collection = $this->_attributeCollection->create()
            ->setEntityTypeFilter(self::ENTITY_TYPE_ID)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        return $collection;
    }

    public function attrCollection()
    {
        $arr= [];

        $model = $this->matrixattributeFactory->create()
                ->getCollection()
                ->addFieldToFilter('status', ['eq'=>1])
                ->getData();
        foreach ($model as $k=>$v) {
            /*$usedincreateform = $this->getForms($v['attribute_id']);
            if (in_array("customer_account_create", $usedincreateform) && $v['status'] == 1)
            {
                $arr[]= $v['attribute_code'];
            }*/
            $arr[]= $v['attribute_code'];

        }
        $attribute = $this->entityAttribute->create()->getCollection()->addFieldToFilter('entity_type_id', self::ENTITY_TYPE_ID);
        $attribute->addFieldToFilter('attribute_code', [
            ['in' => $arr]
        ]);
        return $attribute;
    }

    /*public function getCustomer()
    {
        $customerId = $this->_session->getCustomer()->getId();
        $customerData = $this->_customer->create()->load($customerId);
        return $customerData;
    }*/

    /*public function getForms($id)
    {
        $attributeModel = $this->_attributeFactory->create();
        return $attributeModel->load($id)->getUsedInForms();
    }*/

    public function cmp($a, $b)
    {
        if ($a['status'] == $b['status']) {
            return 0;
        }
        return ($a['status'] < $b['status']) ? -1 : 1;
    }

    public function getRfqEntity()
    {
        $rfqEntity = null;
        $nonCatalofRfq = $this->getNonCatalogQuote();
        if($nonCatalofRfq && $nonCatalofRfq->getRfqId()) {
            $rfqId =  $nonCatalofRfq->getRfqId();
            if(!isset($rfqId) || $rfqId<=0) {
                return null;
            }
            $model = $this->rfqentityFactory->create()
             ->getCollection()
             ->addAttributeToSelect('*')
             ->addAttributeToFilter('rfq_id', ['eq'=>$rfqId])
             ->getFirstItem()
             ->getData();
            if(count($model)) {
                $rfqEntityid = $model['entity_id'];
                $rfqEntity = $this->rfqentityFactory->create()->load($rfqEntityid);
            }

        }

        return $rfqEntity;

    }

    public function getNonCatalogQuote()
    {
        $nonCatalofRfq = $this->_coreRegistry->registry('current_noncatquote');
        return $nonCatalofRfq;
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
