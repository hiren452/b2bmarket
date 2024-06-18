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
 * @package     Ced_CsRfq
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Block\Rfq;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Settings extends \Ced\CsMarketplace\Block\Vendor\Profile\Edit
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vsettingsFactory;

    /**
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory
     * @param \Ced\CsRfq\Model\Settings $rfqSettings
     */
    public function __construct(
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $attributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VsettingsFactory $vsettingsFactory,
        \Ced\CsRfq\Model\Settings $rfqSettings
    ) {
        $this->_formFactory = $formFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vsettingsFactory = $vsettingsFactory;
        $this->rfqSettings = $rfqSettings;
        parent::__construct(
            $vendorFactory,
            $customerFactory,
            $context,
            $customerSession,
            $urlFactory,
            $formFactory,
            $attributeFactory,
            $storeManager,
            $timezone
        );
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $vendor = $this->getVendorId() ? $this->getVendor() : [];
        $code = 'rfq';
        if ($vendor && $vendor->getId()) {
            $method = $this->getSettings();
            $form =  $this->_formFactory->create();
            $form->setAction($this->getUrl('*/*/save', ['section'=>\Ced\CsRfq\Model\Settings::RFQ]))
                ->setId('form-validate')
                ->setMethod('POST')
                ->setEnctype('multipart/form-data')
                ->setUseContainer(true);

            $fields = $method->getFields();

            if (count($fields) > 0) {
                $fieldset = $form->addFieldset('csmarketplace_' . $code, ['legend'=>$method->getLabel('label')]);
                foreach ($fields as $id=>$field) {
                    $key = strtolower(\Ced\CsRfq\Model\Settings::RFQ . '/' . $method->getCode() . '/' . $id);
                    $value = '';
                    $vendor_id = $this->csmarketplaceHelper->getTableKey('vendor_id');
                    $key_tmp = $this->csmarketplaceHelper->getTableKey('key');
                    $setting = $this->vsettingsFactory->create()
                                    ->loadByField([$key_tmp,$vendor_id], [$key,(int)$vendor->getId()]);
                    if($setting) {
                        $value = $setting->getValue();
                    }
                    $fieldset->addField($method->getCode() . $method->getCodeSeparator() . $id, isset($field['type']) ? $field['type'] : 'text', [
                            'label'     												=> $method->getLabel($id),
                            'value'      												=> $value,
                            'name'      												=> 'groups[' . $method->getCode() . '][' . $id . ']',
                            isset($field['class']) ? 'class' : ''   						=> isset($field['class']) ? $field['class'] : '',
                            isset($field['required']) ? 'required' : ''    					=> isset($field['required']) ? $field['required'] : '',
                            isset($field['onchange']) ? 'onchange' : ''    					=> isset($field['onchange']) ? $field['onchange'] : '',
                            isset($field['onclick']) ? 'onclick' : ''    					=> isset($field['onclick']) ? $field['onclick'] : '',
                            isset($field['href']) ? 'href' : ''								=> isset($field['href']) ? $field['href'] : '',
                            isset($field['target']) ? 'target' : ''							=> isset($field['target']) ? $field['target'] : '',
                            isset($field['values']) ? 'values' : '' 						=> isset($field['values']) ? $field['values'] : '',
                            isset($field['after_element_html']) ? 'after_element_html' : ''=> isset($field['after_element_html']) ? '<div><small>' . $field['after_element_html'] . '</small></div>' : '',
                    ]);
                }
            }
            $this->setForm($form);
        }
        return $this;
    }

    /**
     * @param $vendorId
     * @return \Ced\CsRfq\Model\Settings
     */
    public function getSettings($vendorId = 0)
    {
        return $this->rfqSettings;
    }
}
