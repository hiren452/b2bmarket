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
 * @package     Ced_CsMultistepreg
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Controller\Adminhtml\Attributes;

class Grid extends \Magento\Backend\App\Action
{

    protected $resultPageFactory = false;

    /**
     * Grid constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->resultPage = $this->resultPageFactory->create();
        $this->resultPage->setActiveMenu('Ced_CsMarketplace::csmarketplace');
        $this->resultPage->getConfig()->getTitle()->set((__('Registration Steps')));
        return $this->resultPage;
    }

    /*
     * Check permission via ACL resource
    */
    protected function _isAllowed()
    {
        return true;
    }
}
