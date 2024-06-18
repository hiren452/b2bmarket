<?php
namespace Matrix\NoncatalogRfqfrom\Block\Adminhtml\View;

use Magento\Backend\Block\Template;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity;
use Magento\Eav\Model\Entity\AttributeFactory as EavAttributeFactory;
use Magento\Framework\Registry;
use Matrix\NoncatalogRfqfrom\Model\AttributeFactory as NoncatalogRfqfromAttributeFactory;
use Matrix\RfqEntity\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Additional extends Template
{
    const ENTITY_TYPE_ID = 10;

    protected $attributeCollectionFactory;
    protected $eavEntity;
    protected $customerFactory;
    protected $session;
    protected $coreRegistry;
    protected $rfqEntityFactory;
    protected $storeManager;
    protected $noncatalogRfqfromAttributeFactory;
    protected $eavAttributeFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        AttributeCollectionFactory $attributeCollectionFactory,
        RfqEntityFactory $rfqEntityFactory,
        Entity $eavEntity,
        CustomerFactory $customerFactory,
        Registry $registry,
        Session $session,
        NoncatalogRfqfromAttributeFactory $noncatalogRfqfromAttributeFactory,
        EavAttributeFactory $eavAttributeFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->rfqEntityFactory = $rfqEntityFactory;
        $this->eavEntity = $eavEntity;
        $this->customerFactory = $customerFactory;
        $this->coreRegistry = $registry;
        $this->session = $session;
        $this->noncatalogRfqfromAttributeFactory = $noncatalogRfqfromAttributeFactory;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->storeManager = $context->getStoreManager();
    }

    public function rfQattrCollection()
    {
        $collection = $this->attributeCollectionFactory->create()
            ->setEntityTypeFilter(self::ENTITY_TYPE_ID)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        return $collection;
    }

    public function attrCollection()
    {
        $arr = [];

        $model = $this->noncatalogRfqfromAttributeFactory->create()->getCollection()
            ->addFieldToFilter('status', ['eq' => 1])
            ->getData();
        foreach ($model as $k => $v) {
            $arr[] = $v['attribute_code'];
        }

        $attribute = $this->eavAttributeFactory->create()->getCollection()
            ->addFieldToFilter('entity_type_id', self::ENTITY_TYPE_ID)
            ->addFieldToFilter('attribute_code', ['in' => $arr]);
        return $attribute;
    }

    public function getRfqEntity()
    {
        $rfqEntity = null;
        $nonCatalofRfq = $this->getNonCatalogQuote();
        if ($nonCatalofRfq && $nonCatalofRfq->getRfqId()) {
            $rfqId = $nonCatalofRfq->getRfqId();
            if (!isset($rfqId) || $rfqId <= 0) {
                return null;
            }
            $model = $this->rfqEntityFactory->create()
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('rfq_id', ['eq' => $rfqId])
                ->getFirstItem()
                ->getData();
            if (count($model)) {
                $rfqEntityid = $model['entity_id'];
                $rfqEntity = $this->rfqEntityFactory->create()->load($rfqEntityid);
            }
        }
        return $rfqEntity;
    }

    public function getNonCatalogQuote()
    {
        $nonCatalofRfq = $this->coreRegistry->registry('matrix_current_quote');
        return $nonCatalofRfq;
    }

    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
