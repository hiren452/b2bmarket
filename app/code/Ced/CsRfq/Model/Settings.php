<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsRfq
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Model;

class Settings extends \Ced\CsMarketplace\Model\Vendor\Payment\Methods\AbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'rfq';
    /**
     *
     */
    const RFQ = "rfq";

    /**
     * @return mixed
     */
    public function getFields()
    {
        $fields = parent::getFields();
        $fields['active'] = [
            'type' => 'select',
            'required' => true,
            'values' => [
                ['label' => __('Yes'),
                    'value' => 1
                ],
               [
                   'label' => __('No'),
                   'value' => 0
               ]
            ]
        ];
        return $fields;
    }

    /**
     * @param $key
     * @return \Magento\Framework\Phrase
     */
    public function getLabel($key)
    {
        switch($key) {
            case 'label':
                return __('Request To Quote Settings');
            case 'active':
                return __('Enable Rfq System');
            default:
                return parent::getLabel($key);
        }
    }
}
