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
 * Class QuoteUpdatedBy
 * @package Ced\RequestToQuote\Model\Source
 */
class QuoteUpdatedBy implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var
     */
    protected $_quoteUpdatedByOptions;

    /**
     * @return mixed
     */
    public function getAllOptions()
    {
        $this->_quoteUpdatedByOptions[] = [
            'value' => 'Customer',
            'label' => __('Customer')
        ];
        $this->_quoteUpdatedByOptions[] = [
            'value' => 'Admin',
            'label' => __('Admin')
        ];
        $this->_quoteUpdatedByOptions[] = [
            'value' => 'Vendor',
            'label' => __('Vendor')
        ];
        return $this->_quoteUpdatedByOptions;
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
        $updatedByOptions = $this->getOptionArray();
        return isset($updatedByOptions[$optionId]) ? $updatedByOptions[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $resultOptions = [];
        foreach ($this->getAllOptions() as $option) {
            $resultOptions[$option['value']] = (string)$resultOptions['label'];
        }
        return $resultOptions;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $result = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAllOption()
    {
        return $this->getOptionArray();
    }
}
