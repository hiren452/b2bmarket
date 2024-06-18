<?php

namespace OX\Auction\Controller\Rewrite\Adminhtml;

use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory as BidDetailsCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;

/**
 * Class CustomizedMassDelete
 * @package OX\Auction\Controller\Rewrite\Adminhtml
 */
class CustomizedMassDelete extends \Ced\CsAuction\Controller\AuctionList\MassDelete
{
    /**
     * @var BidDetailsCollectionFactory
     */
    private $bidDetailsCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Ced\Auction\Model\AuctionFactory $auctionFactory,
        BidDetailsCollectionFactory $bidDetailsCollectionFactory
    ) {
        $this->bidDetailsCollectionFactory = $bidDetailsCollectionFactory;
        parent::__construct($context, $customerSession, $urlFactory, $auctionFactory);
    }

    public function execute()
    {
        $vendorId = $this->_getSession()->getVendorId();
        if (!$vendorId) {
            return $this->_redirect('csauction/auctionlist/index');
        }

        $entityIds = explode(',', $this->getRequest()->getParam('product_id'));
        $redirectBack = false;

        if (!is_array($entityIds) || empty($entityIds)) {
            $this->messageManager->addErrorMessage(__('Please select Product(s).'));
        } else {
            $auctionDeleted = 0;
            try {
                foreach ($entityIds as $entityId) {
                    $auction = $this->auctionFactory->create()->load($entityId);
                    if ($auction->getStatus() == 'closed') {
                        $this->messageManager->addErrorMessage(__('Auction cannot be deleted because it is closed.'));
                    } elseif ($auction->getStatus() == 'processing') {
                        $bidDetails = $this->bidDetailsCollectionFactory->create()
                            ->addFieldToFilter('product_id', $auction->getProductId())
                            ->addFieldToFilter('auction_id', $auction->getId())
                            ->addFieldToFilter('status', 'bidding');
                        if (!empty($bidDetails->getData())) {
                            $this->messageManager->addErrorMessage(__('Auction cannot be deleted because it has started and customers have started bidding.'));
                        }
                    } else {
                        $auction->delete();
                        $auctionDeleted++;
                    }
                }
            } catch (\Exception $e) {
                $redirectBack = true;
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $auctionDeleted)
        );
        return $this->_redirect('csauction/*/index');
    }
}
