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
 * @category  Ced
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\System\Config\Frontend;

/**
 * Class Fieldset (for adding form fieldsets)
 */
class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * Fieldset constructor.
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->websiteFactory = $websiteFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
    }

    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {

        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);
        if ($websitecode = $this->getRequest()->getParam('website')) {
            $website = $this->websiteFactory->create()->load($websitecode);
            if ($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmembership/general/activation') ? 1 : 0;
            }
        } else {
            $active = $this->csmarketplaceHelper->getStoreConfig('ced_csmembership/general/activation') ? 1 : 0;
        }
        $validation = $active ? 0 : 1;

        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $html .= '<tr id="row_' . $field->getHtmlId() . '"><td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $html .= $field->toHtml();
            }
        }

        $html .= $this->_getFooterHtml($element);
        $html .= '<script>
        		var enable=0;

				if(' . $validation . '){
					document.getElementById("' . $element->getHtmlId() . '").style.display="none";
					document.getElementById("' . $element->getHtmlId() . '-state").
					previousElementSibling.style.display="none";
					document.getElementById("' . $element->getHtmlId() . '-state").style.display="none";
				}
				</script>';
        return $html;
    }
}
