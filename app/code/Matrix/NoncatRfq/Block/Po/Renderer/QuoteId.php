<?php
namespace Matrix\NoncatRfq\Block\Po\Renderer;

class QuoteId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \Magento\Backend\Block\Context $context,
        //\Ced\RequestToQuote\Model\QuoteFactory $quote,
        array $data = []
    ) {
        parent::__construct($context, $data);
        //$this->quote = $quote;
    }
    /**
     * Render approval link in each vendor row
     * @param Varien_Object $row
     * @return String
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $rfqId  = $row['rfq_id'];
        $url = $this->getUrl('vendornoncatrfq/rfq/view/id/1/', ['id'=>$rfqId]);
        $html .= '<a target="_blank" title="' . __('View Non-Catalog RFQ') . '" href="' . $url . '">' . $row->getQuoteIncrementId() . '</a>';
        return $html;
    }
}
