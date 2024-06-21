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

namespace Ced\CsMembership\Block;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class View (for membership product view)
 */
class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var mixed
     */
    protected $_membership;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    protected $_session;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Ced\CsMembership\Model\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * View constructor.
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsMembership\Model\SubscriptionFactory $subscriptionFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);
        $this->_session = $customerSession;
        $this->membershipFactory = $membershipFactory;
        $this->categoryFactory = $categoryFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->getAllowedCatagories();
    }

    /**
     * Get membership details
     *
     * @return mixed
     */
    public function getMembershipDetails()
    {
        if (!$this->_membership) {
            $this->_membership = $this->membershipFactory->create()->load($this->getRequest()->getParam('id'));
        }
        return $this->_membership;
    }

    /**
     * Get allowed categories
     *
     * @return string
     */
    public function getAllowedCatagories()
    {
        $data = $this->getMembershipDetails();
        $category_array = array_unique(explode(',', $data->getCategoryIds() ?? ''));
        $html = '<span>';
        $html = '<span>';
        foreach ($category_array as $value) {
            $_cat = $this->categoryFactory->create()->load($value);
            if ($_cat->getLevel() == '0' || $_cat->getLevel() == '1') {
                continue;
            }
            $html = $html . $_cat->getName() . '</br>';
        }
        return $html . '</span>';
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        return $refererUrl;
    }

    /**
     * Check assigned membership
     *
     * @return mixed
     */
    public function checkAssignedMembership()
    {
        $vendor_id = $this->_session->getvendorId();
        $collection = $this->subscriptionFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $vendor_id)
            ->addFieldToFilter('subscription_id', $this->getRequest()->getParam('id'))
            ->getData();
        return $collection;
    }

    /**
     * Get scope config
     */
    public function getStoreManager()
    {
        return $this->_storeManager;
    }
}
