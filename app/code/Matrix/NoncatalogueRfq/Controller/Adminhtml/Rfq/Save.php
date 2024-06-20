<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Rfq;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\Message;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Message
     */
    protected $message;

    protected $messageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        \Matrix\NoncatalogueRfq\Model\MessageFactory $messageFactory,
        Message $message,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
        $this->message = $message;
        $this->messageFactory = $messageFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $postdata = $this->getRequest()->getPostValue();
            $currentQuote = $this->quoteFactory->create()->load($id);

            if ($currentQuote && $currentQuote->getId()) {
                if ($postdata['message']) {
                    $customer_id = $currentQuote->getCustomerId();
                    $newMessage = $this->messageFactory->create();
                    $newMessage->setData('customer_id', $customer_id);
                    $newMessage->setData('quote_id', $currentQuote->getRfqId());
                    $newMessage->setData('vendor_id', 0);
                    $newMessage->setData('message', $postdata['message']);
                    $newMessage->setData('sent_by', 'Admin');
                    $newMessage->save();
                    $this->messageManager->addSuccessMessage(__('You have successfully replyed to message.'));
                    return $this->_redirect('noncatalogrfq/rfq/edit/', ['id' => $this->getRequest()->getParam('id')]);

                }
                /*$this->_coreRegistry->register('matrix_current_quote', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Non-Catalog Quote # %1', $currentQuote->getQuoteIncrementId()));
                return $resultPage;*/
            }
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
            return $this->_redirect('noncatalogrfq/rfq/edit/', ['id' => $this->getRequest()->getParam('id')]);
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('noncatalogrfq/rfq/edit/', ['id' => $this->getRequest()->getParam('id')]);
    }
}
