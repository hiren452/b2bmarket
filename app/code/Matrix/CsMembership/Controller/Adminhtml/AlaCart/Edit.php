<?php

namespace Matrix\CsMembership\Controller\Adminhtml\AlaCart;

class Edit extends \Magento\Backend\App\Action
{

    protected $alaCartPriceFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registerInterface;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $registerInterface
     * @param \Matrix\CsMembership\Model\AlaCartPriceFactory $alaCartPriceFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registerInterface,
        \Matrix\CsMembership\Model\AlaCartPriceFactory $alaCartPriceFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->alaCartPriceFactory = $alaCartPriceFactory;
        $this->_coreRegistry = $registerInterface;
        $this->backendSession = $backendSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $model = $this->alaCartPriceFactory->create();

        $registryObject = $this->_coreRegistry;

        if ($id) {
            $model->load($id);
            $this->_coreRegistry->register("csmembership_alacart_data", $model);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getPage()->getConfig()->getTitle()->set((__('Manage Ala Cart')));
        $this->_view->renderLayout();
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
