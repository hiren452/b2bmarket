<?php

namespace Matrix\CsMembership\Preference\Block\Plans;

use Ced\CsMarketplace\Model\Session;
use Ced\CsMembership\Block\Plans\Listblock;
use Ced\CsMembership\Helper\Data;
use Ced\CsMembership\Model\SubscriptionFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Customer\Model\Session as CustomerSession;

class CustomizedListblock extends Listblock
{
    /**
     * @var Session
     */
    public $session;

    public function __construct(
        CustomerSession $customerSession,
        Data $membershipHelper,
        SubscriptionFactory $subscriptionFactory,
        Resolver $layerResolver,
        Context $context,
        Session $session,
        array $data = []
    ) {
        parent::__construct(
            $customerSession,
            $membershipHelper,
            $subscriptionFactory,
            $layerResolver,
            $context,
            $data
        );
        $this->session = $session;
    }

    /**
     * @return mixed
     */
    protected function _getMembershipCollection()
    {
        // return array();
        if (is_null($this->_membershipCollection)) {
            $filter = $this->getRequest()->getParams();
            $this->_membershipCollection = $this->membershipHelper->getMembershipPlans();
            if (isset($filter['product_list_order']) || isset($filter['product_list_dir'])) {
                $this->_membershipCollection = $this->_membershipCollection->setOrder(
                    'name',
                    $this->getRequest()->getParam('product_list_dir')
                );
            } else {
                $this->_membershipCollection = $this->_membershipCollection->setOrder('price', 'asc');
            }
        }
        return $this->_membershipCollection;
    }

    public function getRunningMembership()
    {
        $vendor_id = $this->customerSession->getvendorId();
        $collection = $this->subscriptionFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', $vendor_id)
            ->addFieldToFilter('status', \Ced\CsMembership\Model\Status::STATUS_RUNNING)
            ->addFieldToSelect('*');
        $subscription = [];
        foreach ($collection as $key => $value) {
            $subscription[] = $value->getSubscriptionId();
        }
        return $collection->getData();
    }

    public function displayChart()
    {
        return $this->_scopeConfig->getValue(
            'auction_entry_1/standard/show_commission_chart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
