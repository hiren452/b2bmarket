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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultistepreg\Helper;

use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfigManager;

    public $_storeManager;

    protected $vendorAttributeCollection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vendorAttributeCollection
    ) {
        $this->_scopeConfigManager = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->vendorAttributeCollection = $vendorAttributeCollection;
        parent::__construct($context);
    }

    public function isEnabled()
    {
        $enabled = $this->_scopeConfigManager->getValue('ced_csmarketplace/multistepregistration/activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $enabled;
    }

    public function checkStepData()
    {
        $attributes_collection = $this->vendorAttributeCollection->create()
                                ->setStoreId($this->_storeManager->getStore()->getId())
                                ->getCollection();

        $attributes_collection->getSelect()->where('(`vform`.`registration_step_no`>0)');
        $attribute_count = $attributes_collection->count();
        if ($attribute_count==0) {
            return false;
        }
        return true;
    }
}
