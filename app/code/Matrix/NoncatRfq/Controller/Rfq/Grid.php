<?php

namespace Matrix\NoncatRfq\Controller\Rfq;

class Grid extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * Grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
