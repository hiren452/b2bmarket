<?php
namespace Matrix\RegistrationForm\Block;

class Elements extends \Magento\Framework\View\Element\Template
{
    private $data;

    public function setAttributeData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getAttributeData()
    {
        return $this->data;
    }
}
