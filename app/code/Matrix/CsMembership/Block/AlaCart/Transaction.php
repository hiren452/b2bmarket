<?php

namespace Matrix\CsMembership\Block\AlaCart;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;

class Transaction extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    /**
     * @var null
     */
    protected $_subscription = null;

    /**
     * @var \Matrix\CsMembership\Model\AlaCartFactory
     */
    protected $alaCartFactory;

    /**
     * Transaction constructor.
     * @param \Matrix\CsMembership\Model\AlaCartFactory $alaCartFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\Session $customerSession
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        \Matrix\CsMembership\Model\AlaCartFactory $alaCartFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        \Ced\CsMarketplace\Model\Session $customerSession,
        UrlFactory $urlFactory
    ) {
        parent::__construct($vendorFactory, $customerFactory, $context, $customerSession, $urlFactory);

        $this->alaCartFactory = $alaCartFactory;

        $collection = $this->alaCartFactory->create()
            ->getCollection()
            ->addFieldToFilter('vendor_id', $this->_getSession()->getVendorId());
        $this->setCollection($collection);
    }

    /**
     * @return $this|\Ced\CsMarketplace\Block\Vendor\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setLimit(10)
                ->setCollection(
                    $this->getCollection()
                );

            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
