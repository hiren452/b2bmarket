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
 * @package     Ced_CsMembership
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Model;

/**
 * Class Duration (Getting duration for ui form)
 */
class Duration implements \Magento\Framework\Data\OptionSourceInterface
{
    const ONE_MONTH    = 1;
    const THREE_MONTH   = 3;
    const SIX_MONTH  = 6;
    const TWELVE_MONTH  = 12;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializerInterface;

    /**
     * Duration constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializerInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_serializerInterface = $serializerInterface;
    }

    /**
     * Get option array
     *
     * @return array|array[]
     */
    public function getOptionArray()
    {
        $durationCosts = $this->_scopeConfig->getValue(
            'ced_csmarketplace/membership_form_fields/duration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $options = [];
        if ($durationCosts) {
            $durationCosts = $this->_serializerInterface->unserialize($durationCosts);
            foreach ($durationCosts as $key => $value) {
                if ($key == '__empty') {
                    continue;
                }
                if ($value['duration']) {
                    $options[] = [
                        'value' => $value['duration'],
                        'label' => __($value['duration'] . ' Month(s)')
                    ];
                }
            }
        } else {
            $options = [
                ['value' => self::ONE_MONTH, 'label' => __('1 Month(s)')],
                ['value' => self::THREE_MONTH, 'label' => __('3 Month(s)')],
                ['value' => self::SIX_MONTH, 'label' => __('6 Month(s)')],
                ['value' => self::TWELVE_MONTH, 'label' => __('12 Month(s)')]
            ];
        }
        return $options;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return $this->getOptionArray();
    }
}
