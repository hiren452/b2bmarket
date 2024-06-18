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
 * @category  Ced
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Block\Adminhtml\Auction\Create;

/**
 * Adminhtml sales order create block
 *
 * @api
 * @author Magento Core Team <core@magentocommerce.com>
 * @since  100.0.2
 */
class Product extends \Magento\Backend\Block\Widget
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('auction_create_customer');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Please select a product for auction');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    /* public function getButtonsHtml()
    {
        if ($this->_authorization->isAllowed('Magento_Catalog::manage')) {
            $addButtonData = [
                'label' => __('Back'),
                'class' => 'back',
                'onclick' => 'setLocation("' . $this->getBackUrl() . '")',
            ];
            return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
                ->setData($addButtonData)
                ->toHtml();
        }
        return '';
    }*/

    /**
     * @return string
     */
    /*public function getBackUrl()
    {
        return $this->getUrl('auction/addAuction/index');
    }*/
}
