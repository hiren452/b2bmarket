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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Matrix\CsMultistepreg\Block\Multistep;

use Ced\CsMarketplace\Model\Vendor\AttributeFactory;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template\Context;

class Steps extends \Ced\CsMultistepreg\Block\Multistep\Steps
{
    public function __construct(
        Context $context,
        Http $request,
        Session $customerSession,
        \Ced\CsMultistepreg\Model\Steps $stepsCollection,
        AttributeFactory $attributeCollection,
        VendorFactory $vendorFactory
    ) {
        parent::__construct($context, $request, $customerSession, $stepsCollection, $attributeCollection, $vendorFactory);
        parent::setTemplate('csmultistepregistration/multisteps/steps.phtml');
    }

    public function getStepattributes($step)
    {
        if ($step) {
            $collection = $this->attributeCollection->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->getCollection()
                ->addFieldToFilter('registration_step_no', $step)
                ->setOrder('position_in_registration', 'ASC');
            return $collection;
        }
    }
}
