<?php

namespace Matrix\CsMembership\Controller\Membership;

class Upgrade extends \Ced\CsMarketplace\Controller\Vendor
{

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Upgrade Membership'));
        return $resultPage;
    }
}
