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

namespace Ced\CsMembership\Block\History;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class History (for getting membership subscription history)
 */
class History extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var null
     */
    protected $_subscription = null;

    /**
     * @var int
     */
    protected $_defaultColumnCount = 3;

    /**
     * @var
     */
    public $_scopeConfig;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    public $session;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    public $vproductsFactory;

    /**
     * History constructor.
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        UrlFactory $urlFactory
    ) {
        $this->subscriptionFactory = $subscriptionFactory;
        $this->vproductsFactory = $vproductsFactory;
        $this->membershipHelper = $membershipHelper;
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $this->session->getVendorId());
        $this->setCollection($collection);
    }

    /**
     * Prepare layout
     *
     * @return $this
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
                'membership.pager'
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

    /**
     * Get scope config
     */
    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    /**
     * Get Product Limit
     *
     * @return string
     */
    public function getProductLimit()
    {
        return $this->membershipHelper->getLimit(0);
    }
}
