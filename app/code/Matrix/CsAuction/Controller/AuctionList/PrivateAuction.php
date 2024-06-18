<?php

namespace Matrix\CsAuction\Controller\AuctionList;

use Ced\CsMarketplace\Controller\Vendor;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class PrivateAuction extends Vendor
{
    /**
     * @return ResponseInterface|ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {

        if (!$this->_getSession()->getVendorId()) {

            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Invite Buyers'));
        return $resultPage;
    }
}
