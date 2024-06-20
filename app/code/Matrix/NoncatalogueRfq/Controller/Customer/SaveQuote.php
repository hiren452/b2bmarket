<?php
namespace Matrix\NoncatalogueRfq\Controller\Customer;

use Matrix\NoncatalogueRfq\Model\Message;

class SaveQuote extends \Magento\Framework\App\Action\Action
{

    /**
     * @var Message
     */
    protected $message;

    protected $messageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Matrix\NoncatalogueRfq\Model\NoncatalogRfq  $quote,
        \Matrix\NoncatalogueRfq\Model\MessageFactory $messageFactory,
        Message $message,
        array $data = []
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->_getSession = $customerSession;
        $this->_quote = $quote;
        $this->message = $message;
        $this->messageFactory = $messageFactory;
        parent::__construct($context, $data);
    }

    public function execute()
    {
        if (! $this->_getSession->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $this->_redirect('customer/account/login');
            return;
        }

        $postdata = $this->getRequest()->getParams();
        $quote_id = $this->getRequest()->getParam('quoteId');
        $message_id = $this->getRequest()->getParam('message_id');
        $customer_id = $this->_getSession->getCustomer()->getId();
        $rfqModel =$this->_quote->load($quote_id);
        $messageModel = $this->message->load($message_id);
        $rfqcustomerId = $this->_quote->load($quote_id)->getCustomerId();
        $mssgeCustomerId = $messageModel->getCustomerId();
        if ($customer_id == $rfqcustomerId) {
            //if($mssgeCustomerId == $customer_id ){
            if ($messageModel->getId()>0 && $this->getRequest()->getParam('send') && $postdata['message']) {
                $newMessage = $this->messageFactory->create();
                $newMessage->setData('customer_id', $mssgeCustomerId);
                $newMessage->setData('quote_id', $messageModel->getQuoteId());
                $newMessage->setData('vendor_id', $messageModel->getVendorId());
                $newMessage->setData('message', $postdata['message']);
                $newMessage->setData('sent_by', 'Customer');
                $newMessage->save();
                $this->messageManager->addSuccessMessage(__('You have successfully replyed to message.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);
            } else {

                $newMessage = $this->messageFactory->create();
                $newMessage->setData('customer_id', $mssgeCustomerId);
                $newMessage->setData('quote_id', $rfqModel->getRfqId());
                $newMessage->setData('vendor_id', 0);
                $newMessage->setData('message', $postdata['message']);
                $newMessage->setData('sent_by', 'Customer');
                $newMessage->save();
                $this->messageManager->addSuccessMessage(__('Message successfully send.'));
                return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $this->getRequest()->getParam('quoteId')]);

            }
            /*} else {
                 $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update your quotes only.'));
                  return $this->_redirect ('noncatalogrequesttoquote/customer/editquote',['quoteId' => $quote_id] );
            }*/

        } else {
            $this->messageManager->addErrorMessage(__('You are not allowed to update this quote. Kindly update the available quotes only.'));
            return $this->_redirect('noncatalogrequesttoquote/customer/editquote', ['quoteId' => $quote_id]);
        }
    }
}
