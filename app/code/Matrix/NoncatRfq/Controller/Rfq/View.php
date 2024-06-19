<?php
namespace Matrix\NoncatRfq\Controller\Rfq;

use Ced\RequestToQuote\Model\QuoteFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;

use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\NoncatalogRfqFactory;

class View extends \Ced\CsMarketplace\Controller\Vendor
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
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor,
        Registry $coreRegistry,
        NoncatalogRfqFactory $quoteFactory
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
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
                $this->_coreRegistry->register('current_noncatquote', $currentQuote);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('Non-Catalog RFQ # %1', $currentQuote->getQuoteIncrementId()));

                return $resultPage;
            }
            $this->messageManager->addErrorMessage(__('This quote no longer exist.'));
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('vendornoncatrfq/rfq/index');
    }

}
