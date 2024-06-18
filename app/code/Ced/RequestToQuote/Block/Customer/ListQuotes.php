<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\RequestToQuote\Block\Customer;

use Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory;
use Ced\RequestToQuote\Model\Source\QuoteStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class ListQuotes
 * @package Ced\RequestToQuote\Block\Customer
 */
class ListQuotes extends \Magento\Framework\View\Element\Template
{

    /**
     * Quote items per page.
     * @var int
     */
    private $itemsPerPage = 10;

    /**
     * @var CollectionFactory
     */
    protected $quoteCollection;

    /**
     * @var QuoteStatus
     */
    protected $quoteStatus;

    /**
     * ListQuotes constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param QuoteStatus $quoteStatus
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        QuoteStatus $quoteStatus,
        Data $priceingHelper
    ) {
        $this->session = $customerSession;
        $this->quoteCollection = $collectionFactory;
        $this->quoteStatus = $quoteStatus;
        $this->priceingHelper = $priceingHelper;
        parent::__construct($context);
        if ($this->getRequest()->getParam('limit')) {
            $this->itemsPerPage = $this->getRequest()->getParam('limit');
        }
    }

    /**
     *
     */
    public function _construct()
    {
        $this->setTemplate('customer/listquotes.phtml');
        $this->getUrl();
        $customer_Id = $this->session->getCustomerId();
        $quoteModel = $this->quoteCollection->create()
            ->addFieldtoFilter('customer_id', ['customer_id' => $customer_Id])
            ->setOrder('quote_id', 'DESC');

        $this->setCollection($quoteModel);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'my.custom.pager'
            )->setLimit($this->itemsPerPage)->setCollection($this->getCollection());
            $this->setChild('pager', $pager);
        }
        $this->pageConfig->getTitle()->set("My Quotes");
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public function getStatus($status)
    {
        return $this->quoteStatus->getFrontendOptionText($status);
    }
}
