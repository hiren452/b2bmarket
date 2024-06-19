<?php
namespace Matrix\RfqEntity\Controller\Adminhtml\RfqEntity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Validate extends Action
{

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $response;

    /**
     * @param Context     $context
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->response = new \Magento\Framework\DataObject();
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     */
    public function validateRequireEntries(array $data)
    {
        $requiredFields = [
            'identifier' => __('Non-Catalog RFQ Entity Identifier'),
        ];
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $this->_addErrorMessage(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }
    }

    /**
     * Add error message validation
     *
     * @param $message
     */
    protected function _addErrorMessage($message)
    {
        $this->response->setError(true);
        if (!is_array($this->response->getMessages())) {
            $this->response->setMessages([]);
        }
        $messages = $this->response->getMessages();
        $messages[] = $message;
        $this->response->setMessages($messages);
    }

    /**
     * AJAX customer validation action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->response->setError(0);

        $this->validateRequireEntries($this->getRequest()->getParams());

        $resultJson = $this->jsonFactory->create()->setData($this->response);
        return $resultJson;
    }
}
