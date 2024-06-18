<?php
namespace Matrix\NoncatalogRfqfrom\Helper;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Data helper
 */
class Data extends AbstractHelper
{

    protected $attributeFactory;

    public function __construct(
        \Matrix\NoncatalogRfqfrom\Model\AttributeFactory $attributeFactory,
        EavConfig $eavConfig
    ) {
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->eavConfig = $eavConfig;
    }

    public function getAttributeById($id)
    {
        return $this->attributeFactory->create();
    }
    public function getConfig()
    {
        return $this->eavConfig;
    }
}
