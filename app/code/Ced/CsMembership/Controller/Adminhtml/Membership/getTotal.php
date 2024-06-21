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

namespace Ced\CsMembership\Controller\Adminhtml\Membership;

use Magento\Backend\App\Action;

/**
 * Class getTotal (Get total price after changing some field)
 */
class getTotal extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * getTotal constructor.
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Action\Context $context
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->membershipHelper = $membershipHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $membershipData = $this->getRequest()->getParams();
        $total = 0;
        $basePrice = $this->membershipHelper->getBasePrice();
        $categoriesCost = $this->membershipHelper->getCategoriesCost($membershipData['category_ids']);
        $productCost = $this->membershipHelper->getProductCost($membershipData['product_limit']);
        $durationCost = $this->membershipHelper->getDurationCost($membershipData['duration']);
        $total = $basePrice + $categoriesCost + $productCost + $durationCost;
        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($total));
    }
}
