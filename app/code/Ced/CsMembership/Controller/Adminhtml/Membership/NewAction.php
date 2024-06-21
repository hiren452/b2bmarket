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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Controller\Adminhtml\Membership;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class NewAction (for adding new plan)
 */
class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * NewAction constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Backend\App\Action\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registerInterface
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Backend\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registerInterface
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registerInterface;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $prodcutLimit = $this->_scopeConfig->getValue(
            'ced_vproducts/general/limit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $isAllcat = $this->_scopeConfig->getValue(
            'ced_vproducts/general/category_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isAllcat) {
            $categories = $this->_scopeConfig->getValue(
                'ced_vproducts/general/category',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            $category = $this->categoryFactory->create();
            $catTree = $category->getTreeModel()->load();
            $catIds = $catTree->getCollection()->getAllIds();
            $categories = implode(',', $catIds);
        }
        $response = [
            'product_limit' => $prodcutLimit,
            'product_categories' => $categories
        ];
        $this->_coreRegistry->register('csmembership_group_data', $response);
        $this->_coreRegistry->register('csmembership_category', $categories);
        $this->_forward('edit');
    }
}
