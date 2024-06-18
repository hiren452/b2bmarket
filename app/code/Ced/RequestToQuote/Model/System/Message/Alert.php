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

namespace Ced\RequestToQuote\Model\System\Message;

/**
 * Class Alert
 * @package Ced\RequestToQuote\Model\System\Message
 */
class Alert implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var null
     */
    protected $quoteCollection = null;

    /**
     * Alert constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Ced\RequestToQuote\Model\ResourceModel\Quote\CollectionFactory $collectionFactory
    ) {
        $this->_authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;
        $this->_cacheTypeList = $cacheTypeList;
        $this->quoteCollectionFactory = $collectionFactory;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return sha1('RequestToQuote_Alert');
    }

    /**
     * @return \Ced\RequestToQuote\Model\ResourceModel\Quote\Collection|null
     */
    protected function getQuoteCollection()
    {
        if (!$this->quoteCollection) {
            $this->quoteCollection = $this->quoteCollectionFactory->create()
                                          ->addFieldToFilter('status', \Ced\RequestToQuote\Model\Quote::QUOTE_STATUS_PENDING);
        }
        return $this->quoteCollection;
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        $pendingQuote = $this->getQuoteCollection()->getData();
        if(count($pendingQuote)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getText()
    {
        $pendingQuote = $this->getQuoteCollection()->getSize();
        $html = "";
        $html.= $pendingQuote . " Quote requests are Pending." . '<a href="' . $this->_urlBuilder->getUrl('requesttoquote/quotes/view') . '">' . __(' View More') . '</a>' . '<br>';
        return $html;
    }

    /**
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }
}
