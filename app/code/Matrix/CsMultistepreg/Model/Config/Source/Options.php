<?php

namespace Matrix\CsMultistepreg\Model\Config\Source;

use B2bmarkets\Custom\Helper\Data;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Options extends AbstractSource
{
    protected $helperData;

    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    public function getAllOptions()
    {
        $industry = $this->helperData->getIndustries();
        $options[] = ['label' => " ", 'value' => ""];
        foreach ($industry as $key => $value) {
            $options[] = ['label' => $value, 'value' => $key];
        }
        return $options;
    }
}
