<?php

namespace Matrix\CsMembership\Controller\Membership;

class AlaCart extends \Ced\CsMarketplace\Controller\Vendor
{

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Ala Cart'));
        return $resultPage;
    }
}
