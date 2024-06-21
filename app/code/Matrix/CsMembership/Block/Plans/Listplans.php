<?php

namespace Matrix\CsMembership\Block\Plans;

class Listplans extends \Magento\Framework\View\Element\Template
{

    /**
     * @var
     */
    protected $_membershipCollection;

    /**
     * @var int
     */
    protected $_defaultColumnCount = 3;

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CsMembership\Helper\Data $membershipHelper
    ) {
        parent::__construct($context);

        $this->membershipHelper = $membershipHelper;

        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * @return mixed
     */
    public function getLoadedMembershipCollection()
    {
        return $this->_getMembershipCollection();
    }

    /**
     * @return mixed
     */
    protected function _getMembershipCollection()
    {
        if (is_null($this->_membershipCollection)) {
            $this->_membershipCollection = $this->membershipHelper->getMembershipPlans();
            $this->_membershipCollection = $this->_membershipCollection->setOrder('price', 'asc');
        }
        return $this->_membershipCollection;
    }

    public function displayChart()
    {
        return $this->_scopeConfig->getValue(
            'auction_entry_1/standard/show_commission_chart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
