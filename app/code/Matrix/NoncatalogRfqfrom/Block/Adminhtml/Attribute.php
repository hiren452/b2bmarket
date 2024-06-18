<?php
namespace Matrix\NoncatalogRfqfrom\Block\Adminhtml;

class Attribute extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Matrix\NoncatalogRfqfrom\Block\Adminhtml\Attribute\Grid')
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
