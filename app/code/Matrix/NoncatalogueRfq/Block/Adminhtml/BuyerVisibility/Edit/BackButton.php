<?php
namespace Matrix\NoncatalogueRfq\Block\Adminhtml\BuyerVisibility\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Matrix\NoncatalogueRfq\Block\Adminhtml\CategroyUom\Edit\GenericButton;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/');
    }
}
