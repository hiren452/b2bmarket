<?php
namespace Matrix\NoncatalogueRfq\Block\Adminhtml\BuyerVisibility\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Matrix\NoncatalogueRfq\Block\Adminhtml\CategroyUom\Edit\GenericButton;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Update Buyer Visibility'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
