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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMembership\Block\Membership\Edit;

use Ced\CsMembership\Block\Membership\Edit\GenericButton as GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton (for adding delete button in form)
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->getId()) {
            return [
                'label' => __('Delete Membership'),
                'on_click' => sprintf("location.href = '%s';", $this->getDeleteUrl()),
                'class' => 'delete',
                'sort_order' => 10
            ];
        }
        return false;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('csmembership/membership/delete', ['id' => $this->getId()]);
    }
}
