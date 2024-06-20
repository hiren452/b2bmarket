<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * CategoryUom grid collection
 */
class CategroyuomCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    protected $_logger;
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'matrix_category_to_uom',
        $resourceModel = '\Matrix\NoncatalogueRfq\Model\ResourceModel\CategroyUom'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_logger = $logger;
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        //$customer_grid_flat_table = $this->resource->getTableName('customer_grid_flat');
        //$ced_requestquote_table = $this->resource->getTableName('ced_requestquote');
        $catalog_category_entity_table = 'catalog_category_entity';
        $catalog_category_entity_varchar_table = "catalog_category_entity_varchar";
        $ced_requestquote_table = 'ced_requestquote';
        $this->getSelect()->joinInner($catalog_category_entity_table, 'main_table.category_id = ' . $catalog_category_entity_table . '.entity_id', ['entity_id']);
        $this->getSelect()->joinInner($catalog_category_entity_varchar_table, $catalog_category_entity_table . '.entity_id = ' . $catalog_category_entity_varchar_table . '.entity_id AND ' . $catalog_category_entity_varchar_table . '.attribute_id =45 AND ' . $catalog_category_entity_varchar_table . '.store_id=0', ['categoru_name' => 'value']);
        /*$this->getSelect()->joinLeft($customer_grid_flat_table, 'main_table.po_customer_id = '.$customer_grid_flat_table.'.entity_id', ['name']);
        $this->getSelect()->joinLeft($ced_requestquote_table, 'main_table.quote_id = '.$ced_requestquote_table.'.quote_id', ['quote_increment_id']);
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('name', $customer_grid_flat_table.'.name'); */
        $this->_logger->info("MySSQL=" . $this->getSelect());
        return $this;
    }
}
