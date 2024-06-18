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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Plugin\Helper;

use Ced\RequestToQuote\Helper\Data as Helper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class MultishippingHelper
 * @package Ced\RequestToQuote\Plugin\Pricing\Render
 */
class MultishippingHelper
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * MultishippingHelper constructor.
     * @param Helper $helper
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Helper $helper,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Logger $logger
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     */
    public function afterIsMultishippingCheckoutAvailable($subject, $result)
    {
        try {
            if ($this->customerSession->isLoggedIn() && $this->helper->isEnable()) {
                $currentQuote = $this->checkoutSession->getQuote();
                if ($currentQuote && $currentQuote->getId()) {
                    foreach ($currentQuote->getAllItems() as $item) {
                        if ($item->getCedPoId()) {
                            $result = false;
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $result;
    }
}
