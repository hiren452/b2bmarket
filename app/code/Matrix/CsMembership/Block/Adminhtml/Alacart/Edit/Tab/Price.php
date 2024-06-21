<?php

namespace Matrix\CsMembership\Block\Adminhtml\Alacart\Edit\Tab;

class Price extends \Magento\Backend\Block\Widget\Container
{
    protected $alaCartPriceFactory;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Matrix\CsMembership\Model\AlaCartPriceFactory $alaCartPriceFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->alaCartPriceFactory = $alaCartPriceFactory;

        $this->setTemplate('alacart/price.phtml');
    }

    public function getCommission()
    {
        if ($this->getRequest()->getParam('id')) {
            return $this->alaCartPriceFactory->create()->load($this->getRequest()->getParam('id'))->getData('commission');
        }
        return false;
    }
}
