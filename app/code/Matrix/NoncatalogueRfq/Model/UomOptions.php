<?php

namespace Matrix\NoncatalogueRfq\Model;

class UomOptions extends \Magento\Framework\Model\AbstractModel
{

    protected $_storeManager;
    protected $eavConfig;

    /**
     * @var string
     */
    const PRODUCT_ATTRIBUTE_CODE = 'noncatrfq_uom';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct(
            $context,
            $registry
        );

        $this->eavConfig = $eavConfig;
        $this->_storeManager = $storeManager;

        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    public function getUomOptions()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', self::PRODUCT_ATTRIBUTE_CODE);
        $options   = $attribute->getSource()->getAllOptions();
        return $options;
    }
}
