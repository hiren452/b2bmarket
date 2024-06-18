<?php

namespace Ced\CsAuction\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class VendorName
 */
class VendorName extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['vendor_id'])
                    && $item['vendor_id']
                    && isset($item['vendor_name'])
                    && $item['vendor_name']
                ) {
                    $url = $this->urlBuilder->getUrl(
                        'csmarketplace/vendor/edit',
                        [ 'vendor_id' => $item['vendor_id']]
                    );
                    $item[$this->getData('name')] = '<a href="' . $url . '" target="_blank">' . $item['vendor_name'] . '</a>';
                }
            }
        }
        return $dataSource;
    }
}
