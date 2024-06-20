<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * CategoryUom grid collection
 */
class RfqPoCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
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
        $mainTable = 'matrix_noncatalog_po',
        $resourceModel = '\Matrix\NoncatalogueRfq\Model\ResourceModel\RfqPo'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_logger = $logger;
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        //$customer_grid_flat_table = $this->resource->getTableName('customer_grid_flat');
        //$ced_requestquote_table = $this->resource->getTableName('ced_requestquote');
        $customer_grid_flat_table = 'customer_grid_flat';
        $matrix_rfq_table = 'matrix_noncatalog_rfq';
        $this->getSelect()->joinLeft($customer_grid_flat_table, 'main_table.po_customer_id = ' . $customer_grid_flat_table . '.entity_id', ['name']);
        $this->getSelect()->joinLeft($matrix_rfq_table, 'main_table.rfq_id = ' . $matrix_rfq_table . '.rfq_id', ['quote_increment_id']);
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('name', $customer_grid_flat_table . '.name');
        return $this;
    }
}
