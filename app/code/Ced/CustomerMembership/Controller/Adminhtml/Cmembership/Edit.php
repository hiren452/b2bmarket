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
 * @category    Ced
 * @package     Ced_CustomerMembership
 * @author         CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CustomerMembership\Controller\Adminhtml\Cmembership;

use Ced\CustomerMembership\Model\MembershipFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Backend session
     *
     * @var Session
     */
    protected $_backendSession;

    /**
     * Result page factory
     *
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Membership factory
     *
     * @var MembershipFactory
     */
    protected $_membershipFactory;

    /**
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param Session $backendSession
     * @param PageFactory $resultPageFactory
     * @param MembershipFactory $membershipFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        Session $backendSession,
        PageFactory $resultPageFactory,
        MembershipFactory $membershipFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_backendSession = $backendSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_membershipFactory = $membershipFactory;
    }

    /**
     * Initialize action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Ced_CustomerMembership::customermembership')->_addBreadcrumb(__('Manage Membership Plan'), __('Membership Plan'));
        return $this;
    }

    /**
     * Check if the current user is allowed to access this controller action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_CustomerMembership::customermembership');
    }

    /**
     * Edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $membershipModel = $this->_membershipFactory->create();

        if ($id) {
            $membershipModel->load($id);
            if (!$membershipModel->getId()) {
                $this->messageManager->addError(__('This Membership Plan no longer exists.'));
                $this->_redirect('*/*/index');
                return;
            }
        }

        $data = $this->_backendSession->getPageData(true);
        if (!empty($data)) {
            $membershipModel->addData($data);
        }

        $this->_coreRegistry->register('plan_data', $membershipModel);
        $this->_initAction();

        $this->_addBreadcrumb($id ? __('Edit Membership Plan') : __('New Membership Plan'), $id ? __('Edit Membership Plan') : __('New Membership Plan'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $membershipModel->getId() ? $membershipModel->getPlanName() : __('New Membership Plan')
        );

        $this->_view->renderLayout();
    }
}
