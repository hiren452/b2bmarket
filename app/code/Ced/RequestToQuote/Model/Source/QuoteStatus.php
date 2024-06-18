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
 * Class QuoteStatus
 * @package Ced\RequestToQuote\Model\Source
 */
class QuoteStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return mixed
     */
    public function getAllOptions()
    {
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PENDING,
            'label' => __('Pending')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PROCESSING,
            'label' => __('Updated')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED,
            'label' => __('Approved')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_CANCELLED,
            'label' => __('Rejected')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED,
            'label' => __('Complete Proposal')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO,
            'label' => __('Partial Proposal')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED,
            'label' => __('Ordered')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE,
            'label' => __('Complete')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_COMPLETE,
            'label' => __('Partial Complete')
        ];
        return $this->_options;
    }

    /**
     * @return mixed
     */
    public function getFrontendAllOptions()
    {
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PENDING,
            'label' => __('Awaiting Seller\'s Approval')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PROCESSING,
            'label' => __('Seller Updated the Quote')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_APPROVED,
            'label' => __('Quote Approved, Awaiting Seller\'s Proposal')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_CANCELLED,
            'label' => __('Seller Rejected the Quote')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PO_CREATED,
            'label' => __('Seller Created the Proposal')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_PO,
            'label' => __('Seller Created Partial Proposal')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_ORDERED,
            'label' => __('Ordered')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_COMPLETE,
            'label' => __('Quote Items Purchased')
        ];
        $this->_options[] = [
            'value' => \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PARTIAL_COMPLETE,
            'label' => __('Partial Complete')
        ];
        return $this->_options;
    }

    /**
     * @return array
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
     * @param $optionId
     * @return mixed|null
     */
    public function getFrontendOptionText($optionId)
    {
        $options = $this->getFrontendOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * @return array
     */
    public function getFrontendOptionArray()
    {
        $options = [];
        foreach ($this->getFrontendAllOptions() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
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
