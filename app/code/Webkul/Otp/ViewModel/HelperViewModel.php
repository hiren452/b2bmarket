<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\ViewModel;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class HelperViewModel implements ArgumentInterface
{
    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param CompositeConfigProvider $configProvider
     * @param ObjectManager $objectManager
     */
    public function __construct(
        CompositeConfigProvider $configProvider,
        ObjectManager $objectManager
    ) {
        $this->configProvider = $configProvider;
        $this->objectManager = $objectManager;
    }

    /**
     * Get Checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Get helper singleton
     *
     * @param string $className
     * @return AbstractHelper
     * @throws \LogicException
     */
    public function helper($className)
    {
        $helper = $this->objectManager->get($className);
        if (!$helper instanceof AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extend Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
