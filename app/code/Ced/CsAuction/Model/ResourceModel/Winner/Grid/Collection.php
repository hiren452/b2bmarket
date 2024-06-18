<?php

namespace Ced\CsAuction\Model\ResourceModel\Winner\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

// \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
/**
 * Winner grid collection
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory
     */
    private $eavAttributeFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory                                            $entityFactory
     * @param Logger                                                   $logger
     * @param FetchStrategy                                            $fetchStrategy
     * @param EventManager                                             $eventManager
     * @param string                                                   $mainTable
     * @param string                                                   $resourceModel
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttributeFactory
     * @param \Magento\Framework\App\ResourceConnection                $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttributeFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        $mainTable = 'ced_auction_winnerlist',
        $resourceModel = 'Ced\Auction\Model\ResourceModel\Winner'
    ) {
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->resource = $resource;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $ced_csmarketplace_vendor_varchar_table = $this->resource->getTableName('ced_csmarketplace_vendor_varchar');
        $brandAttributeId = $this->eavAttributeFactory->create()
            ->getIdByCode('csmarketplace_vendor', 'public_name');
        $this->getSelect()->joinLeft(
            $ced_csmarketplace_vendor_varchar_table,
            '`main_table`.`vendor_id`=`' . $ced_csmarketplace_vendor_varchar_table . '`.`entity_id` 
                                        AND `' . $ced_csmarketplace_vendor_varchar_table . '`.`attribute_id`=' . $brandAttributeId,
            ['vendor_name' => 'value']
        );
        $this->addFilterToMap('vendor_name', $ced_csmarketplace_vendor_varchar_table . '.value');
        return $this;
    }
}
