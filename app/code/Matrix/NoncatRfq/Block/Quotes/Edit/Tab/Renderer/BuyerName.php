<?php
namespace Matrix\NoncatRfq\Block\Quotes\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class BuyerName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Matrix\NoncatalogueRfq\Helper\Data
     */
    protected $helper;
    /**
     * @param \Matrix\NoncatalogueRfq\Helper\Data $helper
     */
    public function __construct(
        \Matrix\NoncatalogueRfq\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $customerId = $row->getCustomerId();
        $name = '';
        if($this->helper->showBuyerInfo($customerId)) {
            $name = $row->getName();
        } else {
            $name = '<center><i class="fa fa-lock fa-2x" aria-hidden="true"></i></center>';
        }
        return $name;
    }
}
