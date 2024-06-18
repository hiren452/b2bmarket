<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config;

/**
 * Used to get all active payment methods
 */
class AllowedPaymentMethods extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var Config
     */
    private $paymentModelConfig;

    /**
     * @param ScopeConfigInterface $scopeConfigInterface
     *
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfigInterface,
        Config $paymentModelConfig
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->paymentModelConfig = $paymentModelConfig;
    }

    /**
     * ToOptionArray()
     *
     * @return active payment methods from configuration
     */
    public function toOptionArray()
    {
        $payments = $this->paymentModelConfig->getActiveMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfigInterface
                ->getValue('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = [
                'label' => $paymentTitle,
                'value' => $paymentCode
            ];
        }
        return $methods;
    }
}
