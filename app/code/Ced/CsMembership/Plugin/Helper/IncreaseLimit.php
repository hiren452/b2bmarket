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
 * @package     Ced_CsMarketplace
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMembership\Plugin\Helper;

class IncreaseLimit
{
    /**
     * Flatcatalog constructor.
     * @param \Magento\Framework\App\Request\Http $http
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->membershipHelper = $membershipHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundGetVendorProductLimit($subject, callable $proceed)
    {
        $returnValue = $proceed();
        $storeId = $this->storeManager->getStore()->getStoreId();
        $limit = $this->membershipHelper->getLimit($storeId);
        return $limit;
    }
}
