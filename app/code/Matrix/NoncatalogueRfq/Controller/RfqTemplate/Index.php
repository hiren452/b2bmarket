<?php

namespace Matrix\NoncatalogueRfq\Controller\RfqTemplate;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    protected $rfqTemplate;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    protected $_rfqTemplateFactory;

    private $logger;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Matrix\NoncatalogueRfq\Model\RfqTemplate  $rfqTemplate,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqTemplateFactory,
        \Magento\Customer\Model\Session $customerSession,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->_getSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->rfqTemplate = $rfqTemplate;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_rfqTemplateFactory = $rfqTemplateFactory;
        $this->logger            = $logger;
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/stockists_index_index.xml
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {

        try {

            /*if (! $this->_getSession->isLoggedIn ()) {
              return $this->getErrorResult( __ ( 'Please login first' ));
            }

            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->getErrorResult( __ ( 'Invalid access.Please try again.' ));
            }*/
            $postparams         = $this->getRequest()->getParams();
            if (!isset($postparams['form_data'])) {
                return $this->getErrorResult(__('Invalid Data.'));
            }

            $customerId = $this->_getSession->getCustomer()->getId();

            if ($customerId<=0) {
                return $this->getErrorResult(__('Custom not exist.'));
            }

            $formData =  [];
            $this->logger->info("Form Submitted Data", $postparams['form_data']);
            /*foreach($postparams['form_data'] as $key=>$formField){
                $formData[$formField['name']] = $formField['value'];
            }
            $this->logger->info("Form Data",$formData);
            */

            $template_name = $postparams['form_data']['template_name'];// $postparams['form_data'];
            $form_data = json_encode($postparams['form_data']);// $postparams['form_data'];
            $is_emailsend  = 0;
            $status = 1;
            //$unsavedRfqModel = $this->rfqTemplate->load($customerId);
            $templateid  = $postparams['form_data']['rfq_templateid'];
            $rfqtemplate_model = $this->_rfqTemplateFactory->create();
            if (isset($templateid) && $templateid  >0) {
                $rfqtemplate_model = $rfqtemplate_model->load($templateid);
            }
            //$rfqtemplate_model->load($customerId,'customer_id');

            if ($rfqtemplate_model->getId()) {
                //$unsavedRfqModel->setData('customer_id', $customerId);
                $rfqtemplate_model->setData('template_name', $template_name);
                $rfqtemplate_model->setData('form_data', $form_data);
                $rfqtemplate_model->setData('is_emailsend', $is_emailsend);
                $rfqtemplate_model->setData('updated_at', $this->dateTime->gmtDate());
                $rfqtemplate_model->setData('status', $status);

            } else {

                $rfqtemplate_model->setData('customer_id', $customerId);
                $rfqtemplate_model->setData('template_name', $template_name);
                $rfqtemplate_model->setData('form_data', $form_data);
                $rfqtemplate_model->setData('is_emailsend', $is_emailsend);
                $rfqtemplate_model->setData('updated_at', $this->dateTime->gmtDate());
                $rfqtemplate_model->setData('status', $status);

            }
            $rfqtemplate_model->save();

            $result['message'] =  'success';
            $result['status'] =  true;
            return  $this->resultJsonFactory->create()->setData($result);
        } catch (\Exception $exception) {
            //return $this->getErrorResult($exception->getMessage());
            return $this->getErrorResult($exception->getMessage());
        }
    }

    private function getErrorResult($message)
    {
        $result = [];
        $result['options'] =  null;
        $result['message'] =  $message;
        $result['status'] =  false;
        return  $this->resultJsonFactory->create()->setData($result);
    }
}
