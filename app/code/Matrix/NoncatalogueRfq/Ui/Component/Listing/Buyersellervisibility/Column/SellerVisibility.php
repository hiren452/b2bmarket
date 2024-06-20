<?php
namespace Matrix\NoncatalogueRfq\Ui\Component\Listing\Buyersellervisibility\Column;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class SellerVisibility extends \Magento\Ui\Component\Listing\Columns\Column
{

    const CUSTOMER_ATTRIBUTE = 'rfqseller_isvisible';

    protected $_customerRepository;

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
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                $customer  = $this->_customerRepository->getById($item['entity_id']);
                $customAttribute  = $customer->getCustomAttribute(self::CUSTOMER_ATTRIBUTE);
                $isVisible = 0;
                if (isset($customAttribute)) {
                    $isVisible = $customAttribute->getValue();
                }
                $item[$this->getData('name')] =  $this->getFieldLabel($isVisible);
            }
        }

        return $dataSource;
    }

    /**
     * Retrieve field label
     *
     * @param int $item
     * @return string
     */
    private function getFieldLabel(int $rfqbuyerisvisible)
    {

        if ($rfqbuyerisvisible==1) {
            return '<label style="color:#66cd00;font-weight:bold">' . __('Visible') . '</label>';
        } else {
            return '<label style="color:#FF2B2B;font-weight:bold">' . __('Not Visible') . '</label>';
        }
    }
}
