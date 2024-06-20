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

namespace Ced\CsMultistepreg\Block\Multistep;

use Magento\Framework\View\Element\Template\Context;

class Steps extends \Magento\Framework\View\Element\Template
{
    /**
     * @var
     */
    public $vendorData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    public $request;

    /**
     * @var
     */
    public $vendor;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    public $vendorFactory;

    /**
     * Steps constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\CsMultistepreg\Model\Steps $stepsCollection
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeCollection
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMultistepreg\Model\Steps $stepsCollection,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeCollection,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
    ) {
        $this->setTemplate('csmultistepregistration/multisteps/steps.phtml');
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->stepsCollection = $stepsCollection;
        $this->attributeCollection = $attributeCollection;
        $this->request = $request;
        $this->vendorFactory = $vendorFactory;
    }

    /**
     * @return mixed
     */
    public function getcollection()
    {
        $stepsCollection = $this->stepsCollection->getCollection();
        return $stepsCollection;
    }

    /**
     * @param $step
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStepattributes($step)
    {
        if ($step) {
            $collection = $this->attributeCollection->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->getCollection()
                ->addFieldToFilter('registration_step_no', $step)
                ->setOrder('sort_order', 'ASC');
            return $collection;
        }
    }

    /**
     * @return int|null
     */
    public function getRegionId()
    {
        $address = [];
        $address = $this->_customerSession->getData('address');
        if (!empty($address)) {
            $region = $address['region_id'];
            return $region === null ? 0 : $address['region_id'];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function vendorData()
    {
        $id = $this->request->get('id');
        return $this->vendorFactory->create()->load($id)->getData();
    }
}
