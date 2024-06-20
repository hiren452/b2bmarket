<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Membershipfees;

use Matrix\NoncatalogueRfq\Model\MembershipFeesFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var MembershipFeesFactory
     */
    private $membershipFeesFactory;

    protected $dataPersistor;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MembershipFeesFactory $membershipFeesFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->membershipFeesFactory = $membershipFeesFactory;
        $this->_coreRegistry = $registry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        if ($data['rfqmembershipfees']) {
            $id = isset($data['rfqmembershipfees']['id']) ? $data['rfqmembershipfees']['id'] : null;

            $model =  $this->membershipFeesFactory->create();
            $modelLoad =  $this->membershipFeesFactory->create()->load($id);
            if (!$modelLoad->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Non-catalog RFQ Fees no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            //$model->setData($data['categroyuom']);
            $model->setRfqFees($data['rfqmembershipfees']['rfq_fees']);
            $model->setCustomermembershipId($data['rfqmembershipfees']['customermembership_id']);
            if (isset($id) && $id >0) {
                $model->setId($data['id']);
            }
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved Non-catalog RFQ Fees.'));
                //$this->dataPersistor->clear('matrixmedia_adbanner_advert');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Non-catalog RFQ Fees .'));
            }

            $this->dataPersistor->set('matrixmedia_rfqmembership_fees', $data['rfqmembershipfees']);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_NoncatalogueRfq::rfqmembershipfees');
    }
}
