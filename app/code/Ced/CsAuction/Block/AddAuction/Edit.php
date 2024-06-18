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
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Block\AddAuction;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 *
 * @package Ced\CsAuction\Block\AddAuction
 */
class Edit extends Container
{

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'addAuction';
        $this->_blockGroup = 'Ced_CsAuction';

        parent::_construct();
        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
    }

    /**
     * @param  string $buttonId
     * @param  array  $data
     * @param  int    $level
     * @param  int    $sortOrder
     * @param  string $region
     * @return Container|void
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);

    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'csauction/addauction/save',
            ['_current' => true, 'back' => null, 'id' => $this->getRequest()->getParam('id')]
        );
    }

}
