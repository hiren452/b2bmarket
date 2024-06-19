<?php
namespace Matrix\RfqEntity\Setup;

use Magento\Eav\Model\Config;
//use Magento\Eav\Setup\EavSetup;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RfqEntitySetup extends EavSetup
{

    /**
     * Entity type for Non-catalog RFQ  EAV attributes
     */
    const ENTITY_TYPE_CODE = 'mx_rfqentity_rfqentity';

    /**
     * EAV Entity type for  Non-catalog RFQ EAV attributes
     */
    const EAV_ENTITY_TYPE_CODE = 'mx_rfqentity';

    /**
    * EAV configuration
    *
    * @var Config
    */
    protected $eavConfig;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory,
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($setup, $context, $cache, $attrGroupCollectionFactory);
    }

    /**
     * Gets EAV configuration
     *
     * @return Config
     */
    public function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * Retrieve Entity Attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getAttributes()
    {
        $attributes = [];

        $attributes['rfq_id'] = [
            'group' => 'General',
            'type' => 'int',
            'label' => 'Non-catalog RFQ ID',
            'input' => 'text',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'required' => '1',
            'user_defined' => false,
            'default' => '',
            'unique' => false,
            'position' => '10',
            'note' => 'tbl matrix_noncatalog_rfq RFQ ID for better mapping',
            'visible' => '1',
            'wysiwyg_enabled' => '0',
        ];

        // Add your more entity attributes here...

        return $attributes;
    }

    /**
     * Retrieve default entities
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = [
            self::ENTITY_TYPE_CODE => [
                'entity_model' => 'Matrix\RfqEntity\Model\ResourceModel\RfqEntity',
                'attribute_model' => 'Matrix\RfqEntity\Model\ResourceModel\Eav\Attribute',
                'table' => self::ENTITY_TYPE_CODE,
                'increment_model' => null,
                'additional_attribute_table' =>'mx_rfqentity_eav_attribute',
                'entity_attribute_collection' => 'Matrix\RfqEntity\Model\ResourceModel\Attribute\Collection',
                'attributes' => $this->getAttributes(),
            ],
        ];

        return $entities;
    }
}
