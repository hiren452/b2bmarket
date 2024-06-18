<?php
namespace Matrix\NoncatalogRfqfrom\Controller\Adminhtml\Index;

class NewAction extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
