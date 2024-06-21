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

/**
 * Class Edit (for editing membership plan)
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registerInterface;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $registerInterface
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registerInterface,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->membershipFactory = $membershipFactory;
        $this->_coreRegistry = $registerInterface;
        $this->backendSession = $backendSession;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $model = $this->membershipFactory->create();

        $registryObject = $this->_coreRegistry;

        if ($id) {
            $model->load($id);
            $this->_coreRegistry->register("csmembership_data", $model);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $this->_coreRegistry->register("csmembership_data", $model);
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('csmembership_member_data', $model);
        $this->_initAction();
        $this->_addBreadcrumb($id ? __('Edit Membership Plan') : __('New Membership Plan'), $id ? __('Edit Membership Plan') : __('Membership Plan'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getId() ? $model->getName() : __('New Membership Plan')
        );
        $this->_view->renderLayout();
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Ced_CsMembership::view'
        )->_addBreadcrumb(
            __('Membership Plan Manager'),
            __('Membership')
        );
        return $this;
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ced_CsMembership::view');
    }
}
