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
namespace Ced\RequestToQuote\Plugin\Pricing\Render;

use Ced\RequestToQuote\Helper\Data as Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;

/**
 * Class FinalPriceBox
 * @package Ced\RequestToQuote\Plugin\Pricing\Renderdie
 */
class FinalPriceBox
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Http
     */
    private $request;

    /**
     * FinalPriceBox constructor.
     * @param Helper $helper
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Helper $helper,
        CustomerSession $customerSession,
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        Http $request
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterToHtml($subject, $result)
    {
        try {
            if ($this->helper->isEnable()) {
                if (strpos($this->urlBuilder->getUrl('*/*/*'), 'catalog/product/view') !== false &&
                    $productId = $this->request->getParam('id')
                ) {
                    $product = $this->productRepository->getById($productId);
                    if ($product && $product->getId()) {
                        if (!$this->helper->isShowAddToCartAndPrice($product->getTypeId())) {
                            $result = $this->helper->getLoginToSeePriceHtml();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $result;
    }
}
