<?php
namespace Matrix\RfqEntity\Model\ResourceModel\RfqEntity\Grid;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Helper;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Matrix\RfqEntity\Model\ResourceModel\RfqEntity\Collection as RfqEntityCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package RH\HelloWorld\Model\ResourceModel\HelloWorld\Grid
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends RfqEntityCollection implements SearchResultInterface
{

    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactory          $entityFactory
     * @param LoggerInterface        $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface       $eventManager
     * @param Config                 $eavConfig
     * @param ResourceConnection     $resource
     * @param EavEntityFactory       $eavEntityFactory
     * @param Helper                 $resourceHelper
     * @param UniversalFactory       $universalFactory
     * @param StoreManagerInterface  $storeManager
     * @param [type]                 $eventPrefix
     * @param [type]                 $eventObject
     * @param [type]                 $resourceModel
     * @param string                 $model
     * @param AdapterInterface|null  $connection
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Matrix\RfqEntity\Ui\Component\Listing\DataProvider\Document',
        AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $connection
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
