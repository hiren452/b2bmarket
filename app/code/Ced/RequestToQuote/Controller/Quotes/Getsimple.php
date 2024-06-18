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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Controller\Quotes;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Getsimple extends Action
{

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Configurable
     */
    protected $typeConfigurable;

    /**
     * Getsimple constructor.
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param JsonFactory $resultjson
     * @param Configurable $typeConfigurable
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        JsonFactory $resultjson,
        Configurable $typeConfigurable,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->resultJsonFactory = $resultjson;
        $this->typeConfigurable = $typeConfigurable;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $response = [];
        if (isset($data['super_attribute'])) {
            $product = $this->productFactory->create()->load($data['product']);
            $simpleProduct = $this->typeConfigurable->getProductByAttributes($data['super_attribute'], $product);
            $additionalInformation = [
                    'simple_product_id' => $simpleProduct->getId(),
                    'simple_product_name' => $simpleProduct->getName(),
                    'simple_product_sku' => $simpleProduct->getSku(),
                    'super_attribute' => $data['super_attribute']
                ];
            foreach ($data['super_attribute'] as $optionId => $optionValue) {
                $attribute = $this->typeConfigurable->getAttributeById($optionId, $product);
                $additionalInformation['attributes_info'][] = [
                    'label' => $attribute->getAttributeCode(),
                    'value' => $attribute->getSource()->getOptionText($optionValue),
                    'option_id' => $optionId,
                    'option_value' => $optionValue
                ];
            }
            $response = [
                'simple_product_id' => $simpleProduct->getId(),
                'simple_product_name' => $simpleProduct->getName(),
                'custom_option' => base64_encode(json_encode($additionalInformation))
            ];
        }

        if (isset($data['bundle_option'])) {
            $response['bundle_cart_options'] = base64_encode(json_encode($data));
        }
        $response = array_merge($response, $data);
        $this->getResponse()->setBody($response);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
