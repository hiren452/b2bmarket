<?php

namespace Matrix\CustomerMembership\Block\AlaCart;

class Transaction extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;

    protected $customerAlaCartFactory;

    protected $_filtercollection;
    protected $_requestCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Matrix\CustomerMembership\Model\CustomerAlaCartFactory $customerAlaCartFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->customerAlaCartFactory = $customerAlaCartFactory;

        parent::__construct($context, $data);

        $collection = $this->customerAlaCartFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId());
        $this->setCollection($collection);
    }

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
