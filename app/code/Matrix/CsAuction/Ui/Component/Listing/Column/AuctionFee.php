<?php

namespace Matrix\CsAuction\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class VendorName
 */
class AuctionFee extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
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

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['is_paid'])) {

                    if($item['is_paid'] == 1) {

                        if(empty($item['transaction_id'])) {
                            $value = __('Paid');
                        } else {
                            $url = $this->urlBuilder->getUrl(
                                'sales/order/view',
                                [ 'order_id' => $item['transaction_id']]
                            );
                            $value = '<a href="' . $url . '" target="_blank">' . __('Paid') . '</a>';
                        }
                    } else {
                        $value = __('Not Paid');
                    }

                    $item[$this->getData('name')] = $value;
                }
            }
        }
        return $dataSource;
    }
}
