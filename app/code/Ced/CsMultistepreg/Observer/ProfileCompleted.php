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

namespace Ced\CsMultistepreg\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class ProfileCompleted implements ObserverInterface
{

    protected $scopeConfig;

    protected $emailHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Ced\CsMultistepreg\Helper\Email $emailHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->emailHelper = $emailHelper;
    }

    public function execute(Observer $observer)
    {
        $vendor = $observer->getVendor();
        $receiverInfo = [
            'name' => $vendor->getPublicName(),
            'email' => $vendor->getEmail()
        ];

        $adminEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $adminName  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);

        $senderInfo = [
            'name' => $adminName,
            'email' => $adminEmail,
        ];
        $emailTemplateVariables = [];
        $emailTempVariables['name'] = $vendor->getPublicName();
        $emailTempVariables['email'] = $vendor->getEmail();
        $this->emailHelper->sendEmail(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }
}
