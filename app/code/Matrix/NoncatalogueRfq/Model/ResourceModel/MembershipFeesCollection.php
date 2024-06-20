<?php
namespace Matrix\NoncatalogueRfq\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * CategoryUom grid collection
 */
class MembershipFeesCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
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
        $mainTable = 'matrix_rfqmembership_fees',
        $resourceModel = '\Matrix\NoncatalogueRfq\Model\ResourceModel\MembershipFees'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->_logger = $logger;
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        //$customer_grid_flat_table = $this->resource->getTableName('customer_grid_flat');
        //$ced_requestquote_table = $this->resource->getTableName('ced_requestquote');
        $ced_customermembership_table = 'ced_customermembership';
        $this->getSelect()->joinInner($ced_customermembership_table, 'main_table.customermembership_id = ' . $ced_customermembership_table . '.id', ['plan_name']);
        //$this->_logger->info("MySSQL=".$this->getSelect()) ;
        return $this;
    }
}
