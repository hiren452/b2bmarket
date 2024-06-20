<?php

namespace Matrix\NoncatalogueRfq\Controller\Adminhtml\Rfq;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

class Edit extends \Magento\Backend\App\Action
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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->quoteFactory = $quoteFactory;
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
            $currentQuote = $this->quoteFactory->create()->load($id);
            if ($currentQuote && $currentQuote->getId()) {
                $this->_coreRegistry->register('matrix_current_quote', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Non-Catalog Quote # %1', $currentQuote->getQuoteIncrementId()));
                return $resultPage;
            }
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('noncatalogrfq/rfq/index');
    }
}
