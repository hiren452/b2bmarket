<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Po grid collection
 */
class PoCollection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
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
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'ced_request_po',
        $resourceModel = '\Ced\RequestToQuote\Model\ResourceModel\Po'
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $customer_grid_flat_table = $this->getTable('customer_grid_flat');
        $ced_requestquote_table = $this->getTable('ced_requestquote');
        $this->getSelect()->joinLeft($customer_grid_flat_table, 'main_table.po_customer_id = ' . $customer_grid_flat_table . '.entity_id', ['name']);
        $this->getSelect()->joinLeft($ced_requestquote_table, 'main_table.quote_id = ' . $ced_requestquote_table . '.quote_id', ['quote_increment_id']);
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('name', $customer_grid_flat_table . '.name');
        return $this;
    }
}
