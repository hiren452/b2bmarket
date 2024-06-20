<?php
namespace Matrix\NoncatalogueRfq\Model;

use Magento\Framework\Model\AbstractModel;

class NoncatalogRfq extends AbstractModel
{
    const NONCAT_RFQ_STATUS_PENDING = '0';
    const NONCAT_RFQ_STATUS_PROCESSING = '1';
    const NONCAT_RFQ_STATUS_APPROVED = '2';
    const NONCAT_RFQ_STATUS_CANCELLED = '3';
    const NONCAT_RFQ_STATUS_PO_CREATED = '4';
    const NONCAT_RFQ_STATUS_PARTIAL_PO = '5';
    const NONCAT_RFQ_STATUS_ORDERED = '6';
    const NONCAT_RFQ_STATUS_COMPLETE = '7';

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'matrix_noncatalog_rfq';

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Matrix\NoncatalogueRfq\Model\ResourceModel\NoncatalogRfq');
    }

    public static function getStatusArray()
    {
        return [
            self::QUOTE_STATUS_PENDING    => __('Pending'),
            self::QUOTE_STATUS_PROCESSING    => __('Processing'),
            self::QUOTE_STATUS_APPROVED    => __('Approved'),
            self::QUOTE_STATUS_CANCELLED    => __('Cancelled'),
            self::QUOTE_STATUS_PO_CREATED    => __('PO Created'),
            self::QUOTE_STATUS_PARTIAL_PO    => __('Partial PO'),
            self::QUOTE_STATUS_ORDERED    => __('Ordered'),
            self::QUOTE_STATUS_COMPLETE   => __('Complete')
        ];
    }
}
