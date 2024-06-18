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

namespace Ced\RequestToQuote\Model\Source;

/**
 * Class PoStatus
 * @package Ced\RequestToQuote\Model\Source
 */
class PoStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return mixed
     */
    public function getAllOptions()
    {
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED,
            'label' => __('Not Yet Ordered.')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Po::PO_STATUS_ORDERED,
            'label' => __('Ordered')
        ];
        return $this->_options;
    }

    /**
     * @return array|mixed
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * @param $optionId
     * @return mixed|null
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        return $this->getOptionArray();
    }
}
