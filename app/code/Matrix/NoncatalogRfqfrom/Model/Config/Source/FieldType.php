<?php
namespace Matrix\NoncatalogRfqfrom\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class FieldType implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        // TODO: Implement toOptionArray() method.
        $options = [
            0 => [
                'label' => __('Text Field'),
                'value' => 'text'
            ],
            1 => [
                'label' => __('Text Area'),
                'value' => 'textarea'
            ],
            2 => [
                'label' => __('Date'),
                'value' => 'date'
            ],
            4 => [
                'label' => __('Dropdown'),
                'value' => 'select'
            ],
            5 => [
                'label' => __('Multiple Select'),
                'value' => 'multiselect'
            ],
            6 => [
                'label' => __('Yes/No'),
                'value' => 'yes/no'
            ],
            7 => [
                'label' => __('Media Image'),
                'value' => 'image'
            ],
            8 => [
                'label' => __('File'),
                'value' => 'file'
            ],
            9 => [
                'label' => __('Time'),
                'value' => 'is_time'
            ],
        ];

        return $options;
    }
}
