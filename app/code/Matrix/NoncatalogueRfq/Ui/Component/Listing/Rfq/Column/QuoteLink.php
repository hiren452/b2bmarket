<?php

namespace Matrix\NoncatalogueRfq\Ui\Component\Listing\Rfq\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductActions
 */
class QuoteLink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
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

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['quote_increment_id'])) {
                    $url = $this->urlBuilder->getUrl(
                        'noncatalogrfq/rfq/edit',
                        ['id' => $item['rfq_id']]
                    );
                    $item[$this->getData('name')] = '<a href="' . $url . '">' . $item['quote_increment_id'] . '</a>';
                }
            }
        }
        return $dataSource;
    }
}