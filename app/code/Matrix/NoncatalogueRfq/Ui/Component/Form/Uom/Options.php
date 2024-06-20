<?php
namespace Matrix\NoncatalogueRfq\Ui\Component\Form\Uom;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var UomoptionsFactory
     */
    protected $_uomoptionsFactory;

    public function __construct(
        \Matrix\NoncatalogueRfq\Model\UomOptionsFactory $uomoptionsFactory
    ) {
        $this->_uomoptionsFactory =  $uomoptionsFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        $result = [];
        $options = $this->_uomoptionsFactory->create()->getUomOptions();
        if (count($options)) {
            foreach ($options as $key => $option) {
                if ($option['value']>0) {
                    //$result[] = ['value' => $option['value'], 'label' => $option['label']];
                    $result[$option['value']] =$option['label'];
                }
            }
        }
        return $result;
    }
}
