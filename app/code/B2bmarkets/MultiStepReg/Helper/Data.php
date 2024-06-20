<?php

namespace B2bmarkets\MultiStepReg\Helper;

use Magento\Eav\Model\Config as EavConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $eavConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        EavConfig $eavConfig
    ) {
        parent::__construct($context);
        $this->eavConfig = $eavConfig;
    }

    public function getBuyerCompanyTypeAttribute()
    {
        return $this->eavConfig->getAttribute('customer', 'buyer_company_type');
    }
}
