<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\ViewModel;

class ListViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    /**
     * @var \Ced\CsMembership\Helper\Data $advTransactionHelper
     */
    private $csMembershipHelper;

    /**
     * @var \Magento\Catalog\Helper\Image $imageHelper
     */
    private $imageHelper;

    /**
     * @param \Ced\CsMembership\Helper\Data $csMembershipHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Ced\CsMembership\Helper\Data $csMembershipHelper,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->csMembershipHelper = $csMembershipHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @return \Ced\CsMembership\Helper\Data
     */
    public function getCsMembershipHelper()
    {
        return $this->csMembershipHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getCatalogImageHelper()
    {
        return $this->imageHelper;
    }
}
