<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_Customermembership
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CustomerMembership\Block\Membership;

/**
 * Customer address edit block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveStockStatus extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ced\CustomerMembership\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper=$helper;
        if ($this->helper->getModuleStatus()) {
            if (!$this->helper->getMembershipPlanCount()) {
                $layout = $this->getLayout();
                $layout->unsetElement('cmember-plan');
                $layout->unsetElement('cmember');
            }
            if (!$this->helper->subscriptionStatus()) {
                $this->pageConfig->addPageAsset('Ced_CustomerMembership::css/hide.css');
            }
        }
    }
}
