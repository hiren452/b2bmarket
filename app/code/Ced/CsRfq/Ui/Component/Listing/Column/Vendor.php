<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRfq
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Ui\Component\Listing\Column;

use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class ProductActions
 */
class Vendor extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param VendorFactory $vendorFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        VendorFactory $vendorFactory,
        array $components = [],
        array $data = []
    ) {

        $this->vendorFactory = $vendorFactory;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['vendor_id'])) {

                    $vendor = $this->vendorFactory->create()->load($item['vendor_id']);
                    if($vendor->getId()) {
                        $name = $vendor->getPublicName();
                    } else {
                        $name ='admin';
                    }

                    $item['vendor_id'] = $name;
                }
            }
        }
        return $dataSource;
    }
}
