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
 * @package     Ced_CsMembership
 * @author        CedCommerce Core Team <connect@cedcommerce.com>e
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\App;

use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Magento\Framework\App\Config
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    public $_coreRegistry = null;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var ConfigTypeInterface[]
     */
    private $types;

    const SCOPE_TYPE_DEFAULT = 'default';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Registry $registry
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param array $types
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Registry $registry,
        ScopeCodeResolver $scopeCodeResolver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $types = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->_eventManager = $eventManager;
        $this->types = $types;
        $this->state = $state;
        $this->registry = $registry;
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $configPath = $scope;

        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }

        if ($path) {
            $configPath .= '/' . $path;
        }
        $resource = $this->resourceConnection;
        $tablename = $resource->getTableName('core_config_data');
        $connection = $resource->getConnection();
        $sql = $connection->select()->from($resource->getTableName('core_config_data'))
            ->where('path = ?', 'ced_csgroup/general/activation');
        $result = $connection->fetchAll($sql);

        if (($path == 'ced_vproducts/general/limit') || ($path == 'ced_vproducts/general/category') && ($this->state->getAreaCode() != 'adminhtml')) {
            $result = new \Magento\Framework\DataObject();
            $this->_eventManager->dispatch('ced_csgroup_config_data_change_after', ['result' => $result, 'path' => $path, 'groupdata' => $this->get('system', $configPath)]);
            if ($result->getResult()) {
                return $result->getResult();
            }
        }
        if (empty($result)) {

            return $this->get('system', $configPath);
        }
        if ((!empty($result)) && (!empty($result[0])) && ($result[0]['value'] == 0)) {
            return $this->get('system', $configPath);
        }

        $vendorData = $this->registry->registry('vendor');

        if (!$vendorData && !$this->registry->registry('current_order_vendor')) {
            return $this->get('system', $configPath);
        } else {
            if ($vendorData) {
                $groupCode = $vendorData['group'];
            } elseif ($this->registry->registry('current_order_vendor')) {
                $groupCode = $this->registry->registry('current_order_vendor')->getGroup();
            } else {
                return $this->get('system', $configPath);
            }

            $paths = $groupCode . "/" . $path;
            $configPaths = '';
            $configPaths = $scope;
            if ($scopeCode) {
                $configPaths .= '/' . $scopeCode;
            }
            $configData = $this->get('system', $configPaths . '/' . $paths);

            if ($configData != "") {
                return $this->get('system', $configPaths . '/' . $paths);
            } else {
                return $this->get('system', $configPath);
            }
        }
    }

    /**
     * Get Function
     *
     * @param string $configType
     * @param string $path
     * @param null $default
     * @return array|null
     */
    public function get($configType, $path = '', $default = null)
    {
        $result = null;
        if (isset($this->types[$configType])) {
            $result = $this->types[$configType]->get($path);
        }

        return $result !== null ? $result : $default;
    }
}
