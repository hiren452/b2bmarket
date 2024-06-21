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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMemberShip\Model\System\Config\Source;

/**
 * Class Duration (for getting duration)
 */
class Duration extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const MSG = 0;

    const MONTH = 1;

    const QUATOR = 3;

    const HALF = 6;

    const YEAR = 12;

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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\SerializerInterface $serializerInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\SerializerInterface $serializerInterface
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_serializerInterface = $serializerInterface;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::MONTH, 'label' => __('1 Month(s)')],
            ['value' => self::QUATOR, 'label' => __('3 Month(s)')],
            ['value' => self::HALF, 'label' => __('6 Month(s)')],
            ['value' => self::YEAR, 'label' => __('12 Month(s)')]
        ];
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Returns label for value
     *
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     *
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = [];
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

    /**
     * Duration array
     *
     * @param $group
     * @return array
     */
    public function durationArray($group)
    {
        $durationCosts = $this->scopeConfig->getValue(
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
        }
        return $options;
    }
}
