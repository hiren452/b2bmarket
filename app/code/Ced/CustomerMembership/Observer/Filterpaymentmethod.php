<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Observer;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Catalog\Observer\MenuCategoryData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;

class Filterpaymentmethod implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var CartInterface
     */
    protected $_quote;

    /**
     * @var Category
     */
    protected $catalogCategory;

    /**
     * @var State
     */
    protected $categoryFlatState;

    /**
     * @var MenuCategoryData
     */
    protected $menuCategoryData;

    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @param ManagerInterface $eventManager
     * @param CartInterface $quote
     * @param Category $catalogCategory
     * @param State $categoryFlatState
     * @param MenuCategoryData $menuCategoryData
     * @param ViewInterface $view
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        ManagerInterface $eventManager,
        CartInterface $quote,
        Category $catalogCategory,
        State $categoryFlatState,
        MenuCategoryData $menuCategoryData,
        ViewInterface $view,
        ScopeConfigInterface $scopeConfig,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->_eventManager = $eventManager;
        $this->_quote = $quote;
        $this->catalogCategory = $catalogCategory;
        $this->categoryFlatState = $categoryFlatState;
        $this->menuCategoryData = $menuCategoryData;
        $this->_view = $view;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();

        $cid = $this->customerSession->getId();

        if ($this->scopeConfig->isSetFlag('ced_membership/general/ced_customermembership')) {
            $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
            $flag = false;
            foreach ($quoteItems as $item) {
                if (strpos($item->getSku(), "customermembership") !== false) {
                    $flag = true;
                }
            }
            $value = 'payment/' . $method->getCode() . '/group';
            $group = $this->scopeConfig->getValue($value);

            if ($flag && $group == 'offline') {
                $result->setData('is_available', false);
            }
        }
    }
}
