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

namespace Ced\Auction\Block\Adminhtml\Winner;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Back button
     */
    public function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_winner';
        $this->_blockGroup = 'Ced_Auction';
        parent::_construct();

        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('reset');
    }

    /**
     * @return string
     */
    public function getBackUlr()
    {
        return $this->getUrl('auction/winner/details');
    }
}
