<?php

namespace Matrix\CsMembership\Plugin\Controller\Membership;

class Index extends \Ced\CsMarketplace\Controller\Vendor
{
    public function afterExecute(\Ced\CsMembership\Controller\Membership\Index $subject, $result)
    {
        $this->_redirect('subscription/membership/index');
    }
}
