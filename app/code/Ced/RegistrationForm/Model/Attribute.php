<?php
namespace Ced\RegistrationForm\Model;

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Attribute extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {

        $this->_init('Ced\RegistrationForm\Model\ResourceModel\Attribute');
    }
}
