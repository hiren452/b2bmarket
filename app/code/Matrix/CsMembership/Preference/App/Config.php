<?php

namespace Matrix\CsMembership\Preference\App;

use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Ced\CsMembership\App\Config
{

    public $_eventManager;
    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Registry $registry,
        ScopeCodeResolver $scopeCodeResolver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $types = []
    ) {
        parent::__construct(
            $resourceConnection,
            $state,
            $registry,
            $scopeCodeResolver,
            $eventManager,
            $types
        );
        $this->scopeCodeResolver = $scopeCodeResolver;
    }

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

        if ((strpos($path, 'ced_') !== false) && ($this->state->getAreaCode() != 'adminhtml')) {
            $result = new \Magento\Framework\DataObject();
            $this->_eventManager->dispatch('ced_csgroup_config_data_change_after', ['result' => $result, 'path' => $path, 'groupdata' => $this->get('system', $configPath)]);
            if ($result->getResult()) {
                return $result->getResult();
            }
        }

        $vendorData = $this->registry->registry('vendor');

        if (!$vendorData && !$this->registry->registry('current_order_vendor')) {
            return $this->get('system', $configPath);
        } else {
            if ($vendorData) {
                $groupCode = $vendorData['group'];
            } elseif ($this->registry->registry('current_order_vendor') && gettype($this->registry->registry('current_order_vendor')) != "integer") {
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
}
