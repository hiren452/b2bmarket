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
 * @package     Ced_CsMultistepreg
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Block\Adminhtml\Vendor\Entity\Grid\Renderer;

use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMultistepreg\Helper\Data;
use Magento\Backend\Block\Context;
use Magento\Framework\Data\Form\FormKey;

class Approve extends \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity\Grid\Renderer\Approve
{
    protected $helperData;

    protected $vendorFactory;

    /**
     * Approve constructor.
     * @param VendorFactory $vendorFactory
     * @param FormKey $formKey
     * @param Data $helperData
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        VendorFactory $vendorFactory,
        FormKey $formKey,
        Data $helperData,
        Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $formKey, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        $html = '';

        $module = $this->helperData->isEnabled();
        $vendor_multistep = $this->vendorFactory->create()->load($row->getEntityid())->getData('multistep_done');
        if (($module && $vendor_multistep) || !$module) {
            if ($row->getEntityId()!='' && $row->getStatus() != Vendor::VENDOR_APPROVED_STATUS) {
                $url =  $this->getUrl('*/*/massStatus', [
                    'vendor_id' => $row->getEntityId(),
                    'status'=> Vendor::VENDOR_APPROVED_STATUS,
                    'inline'=>1
                ]);

                $html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\'' . __('Are you sure you want to Approve?') . '\', \'' . $url . '\');" >' . __('Approve') . '</a>';
            }

            if ($row->getEntityId()!='' && $row->getStatus() != Vendor::VENDOR_DISAPPROVED_STATUS) {
                if (strlen($html) > 0) {
                    $html .= ' | ';
                }
                $url =  $this->getUrl('*/*/massStatus', [
                    'vendor_id' => $row->getEntityId(),
                    'status'=> Vendor::VENDOR_DISAPPROVED_STATUS,
                    'inline'=>1
                ]);

                $html .= '<a href="javascript:void(0);" onclick="deleteConfirm(\'' . __('Are you sure you want to Disapprove?') . '\', \'' . $url . '\');" >' . __('Disapprove') . "</a>";
            }
        } elseif ($module) {
            $html .= __('Multi Steps Not done');
        } else {
            $html = ' - ';
        }

        return $html;
    }
}
