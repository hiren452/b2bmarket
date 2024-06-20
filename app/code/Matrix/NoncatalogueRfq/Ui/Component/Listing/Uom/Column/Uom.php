<?php

namespace Matrix\NoncatalogueRfq\Ui\Component\Listing\Uom\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Uom extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UomoptionsFactory
     */
    protected $_uomoptionsFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Matrix\NoncatalogueRfq\Model\UomOptionsFactory $uomoptionsFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Matrix\NoncatalogueRfq\Model\UomOptionsFactory $uomoptionsFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_uomoptionsFactory =  $uomoptionsFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $optionsArray =  $this->getOptions();

            $uomOptions = '';
            foreach ($dataSource['data']['items'] as & $item) {
                $uom_options = $item['uom_options'];

                $uom_options_arr =  explode(",", $uom_options);
                if (count($uom_options_arr)) {
                    foreach ($uom_options_arr as $key => $val) {
                        //echo $optionsArray[$val]."<br />";
                        $uomOptions .= $optionsArray[$val] . ", ";
                    }
                }
                $uomOptions =  substr($uomOptions, 0, strlen($uomOptions)-2);

                $item[$this->getData('name')] = $uomOptions;
            }
        }
        return $dataSource;
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
