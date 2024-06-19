<?php

namespace Matrix\NoncatRfq\Controller\Po;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Model\RfqPoFactory;

class ViewPo extends \Ced\CsMarketplace\Controller\Vendor
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
     * @var PoFactory
     */
    protected $poFactory;

    /**
     * ViewPo constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PoFactory $poFactory
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
        RfqPoFactory $poFactory
    ) {
        parent::__construct($context, $resultPageFactory, $customerSession, $urlFactory, $registry, $jsonFactory, $csmarketplaceHelper, $aclHelper, $vendor);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->poFactory = $poFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $currentPo = $this->poFactory->create()->load($id);
            if ($currentPo && $currentPo->getId()) {
                $this->_coreRegistry->register('matrix_noncatalog_current_po', $currentPo);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend('Non-Catalog RFQ Proposal #' . $currentPo->getPoIncrementId());
                return $resultPage;
            } else {
                $this->messageManager->addErrorMessage(__('This Po no longer exist.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong.'));
        }
        return $this->_redirect('vendornoncatrfq/po/index');
    }
}
