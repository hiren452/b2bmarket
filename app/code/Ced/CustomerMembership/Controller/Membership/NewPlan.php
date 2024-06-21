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
 * @package     Ced_CustomerMembership
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CustomerMembership\Controller\Membership;

class NewPlan extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $_custmerSesion;
    protected $_storeScope= \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_custmerSesion = $session;
        parent::__construct($context);
    }
    public function execute()
    {

        $title=$this->_scopeConfig->getValue('ced_membership/cmembership/cmembership_title', $this->_storeScope);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Membership'));
        $resultPage->getConfig()->getTitle()->prepend(__($title));
        return $resultPage;
    }
}
