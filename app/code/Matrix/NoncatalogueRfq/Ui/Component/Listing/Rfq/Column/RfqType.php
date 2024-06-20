<?php

namespace Matrix\NoncatalogueRfq\Ui\Component\Listing\Rfq\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Matrix\NoncatalogueRfq\Helper\Data;

class RfqType extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UomoptionsFactory
     */
    protected $_uomoptionsFactory;

    protected $helper;

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
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_uomoptionsFactory =  $uomoptionsFactory;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
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
            $rfeTypeArray =  $this->getOptions();
            foreach ($dataSource['data']['items'] as & $item) {

                //$item['yourcolumn'] is column name
                $item[$this->getData('name')] = $rfeTypeArray[$item['rfq_type']]; //Here you can do anything with actual data

            }
        }

        return $dataSource;
    }

    public function getOptions()
    {
        $result = [];
        $options = $this->helper->getRFQuoteTypes();
        if (count($options)) {
            foreach ($options as $key => $value) {
                $result[$key] =$value;
            }
        }
        return $result;
    }
}
