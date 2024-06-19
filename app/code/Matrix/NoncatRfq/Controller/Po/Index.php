<?php
namespace Matrix\NoncatRfq\Controller\Po;

class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Non-Catalog RFQ Proposal'));
        return $resultPage;
    }
}
