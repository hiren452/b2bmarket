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
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Plans;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Transaction (for transaction grid)
 */
class Transaction extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var null
     */
    protected $_subscription = null;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * Transaction constructor.
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->subscriptionFactory = $subscriptionFactory;
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 'running')
            ->addFieldToFilter('vendor_id', $this->_getSession()->getVendorId());
        $this->setCollection($collection);
    }

    /**
     * Prepare layout
     *
     * @return $this|\Ced\CsMarketplace\Block\Vendor\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $limit = $this->getRequest()->getParam('limit');
            if ($limit == "") {
                $limit = 10;
            }
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setLimit($limit)
                ->setCollection(
                    $this->getCollection()
                );

            $this->setChild('pager', $pager);
            $this->getCollection()->load();
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
}
