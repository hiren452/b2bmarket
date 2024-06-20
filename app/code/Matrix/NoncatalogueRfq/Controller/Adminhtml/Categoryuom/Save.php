<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Categoryuom;

use Matrix\NoncatalogueRfq\Model\CategroyUomFactory;

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
     * @var CategroyUomRepository
     */
    private $categroyUomRepository;

    /**
     * @var CategroyUomFactory
     */
    private $categroyUomFactory;

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
        CategroyUomFactory $categroyUomFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->categroyUomFactory = $categroyUomFactory;
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
        if ($data['categroyuom']) {
            $id = isset($data['categroyuom']['id']) ? $data['categroyuom']['id'] : null;

            $model =  $this->categroyUomFactory->create();
            $modelLoad =  $this->categroyUomFactory->create()->load($id);
            if (!$modelLoad->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Category UOM  no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            //$model->setData($data['categroyuom']);
            $model->setCategoryId($data['categroyuom']['category_id']);
            $uom_options =  implode(",", $data['categroyuom']['uom_options']);
            $model->setUomOptions($uom_options);
            if (isset($id) && $id >0) {
                $model->setId($data['id']);
            }
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Category UOM .'));
                //$this->dataPersistor->clear('matrixmedia_adbanner_advert');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Category UOM .'));
            }

            $this->dataPersistor->set('matrixmedia_category_uom ', $data['categroyuom']);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Matrix_NoncatalogueRfq::assignuom');
    }
}
