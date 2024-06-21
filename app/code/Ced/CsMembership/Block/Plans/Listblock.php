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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Plans;

use Magento\Customer\Model\Session;

/**
 * Class Listblock (for listing block)
 */
class Listblock extends \Ced\CsMembership\Block\Plans\ListBlock\ListBlock
{
    /**
     * @var null
     */
    public $_subscription = null;

    /**
     * @var
     */
    public $_membershipCollection;

    /**
     * @var int
     */
    public $_defaultColumnCount = 3;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    public $membershipHelper;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    public $subscriptionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * Listblock constructor.
     * @param Session $customerSession
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param array $data
     */
    public function __construct(
        Session $customerSession,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) {
        parent::__construct($layerResolver, $context, $data);

        $this->customerSession = $customerSession;
        $this->membershipHelper = $membershipHelper;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
    }

    /**
     * Set collection
     *
     * @param $collection
     * @return $this|ListBlock\ListBlock
     */
    public function setCollection($collection)
    {
        $this->_membershipCollection = $collection;
        return $this;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function setColumnCount($count = 3)
    {
        return $this->_defaultColumnCount = $count;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getColumnCount()
    {
        return $this->_defaultColumnCount;
    }

    /**
     * Get loaded membership collection
     *
     * @return mixed
     */
    public function getLoadedMembershipCollection()
    {
        return $this->_getMembershipCollection();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_getMembershipCollection()) {
            $limit = $this->getRequest()->getParam('product_list_limit');
            if ($limit == "") {
                $limit = 10;
            }
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'membership.pager'
            )->setLimit($limit)
                ->setCollection(
                    $this->_getMembershipCollection()
                );

            $this->setChild('pager', $pager);
            $this->_getMembershipCollection()->load();
        }
        return $this;
    }

    /**
     * Get pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get membership collection
     *
     * @return mixed
     */
    protected function _getMembershipCollection()
    {
        if ($this->_membershipCollection == null) {
            $filter = $this->getRequest()->getParams();
            $this->_membershipCollection = $this->membershipHelper->getMembershipPlans();
            if (isset($filter['product_list_order']) || isset($filter['product_list_dir'])) {
                $this->_membershipCollection = $this->_membershipCollection->setOrder(
                    'name',
                    $this->getRequest()->getParam('product_list_dir')
                );
            } else {
                $this->_membershipCollection = $this->_membershipCollection->setOrder('name', 'asc');
            }
        }
        return $this->_membershipCollection;
    }

    /**
     * Before html
     *
     * @return ListBlock\ListBlock
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getMembershipCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);

        $this->_getMembershipCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Get assigned membership collection
     *
     * @return array
     */
    public function getAssignedMembershipCollection()
    {
        $vendor_id = $this->customerSession->getvendorId();
        $collection = $this->subscriptionFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', $vendor_id)
            ->addFieldToSelect('subscription_id');

        $subscription = [];
        foreach ($collection as $key => $value) {
            $subscription[] = $value->getSubscriptionId();
        }
        return $subscription;
    }

    /**
     * Get attribute used for sort by array
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = [__('Name')];
        return $options;
    }

    /**
     * Get scope config
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get scope config
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
