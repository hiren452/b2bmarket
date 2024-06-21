<?php

namespace Matrix\CsMembership\Plugin;

use Ced\CsMarketplace\Model\VproductsFactory;
use Ced\CsMembership\Helper\Data;
use Ced\CsProduct\Block\Product\Edit\Button\Save;
use Magento\Customer\Model\Session;

class RestrictProductDuplicate
{
    public $session;
    public $responseFactory;
    public $vproductsFactory;
    public $membershipHelper;

    public function __construct(Data $membershipHelper, Session $session, VproductsFactory $vproductsFactory)
    {
        $this->session = $session;
        $this->membershipHelper = $membershipHelper;
        $this->vproductsFactory = $vproductsFactory;
    }

    /**
     * @param Save $subject
     * @param $result
     */
    public function afterGetButtonData(Save $subject, $result)
    {
        foreach ($result['options'] as $key => $value) {
            if ($value['id_hard'] == "save_and_duplicate") {
                $vendorId = $this->session->getVendorId();
                $existingSubscription = $this->membershipHelper->getExistingSubcription($vendorId);
                if (empty($existingSubscription)) {
                    unset($result['options'][$key]);
                    return $result;
                }
                $Remainingcount = $existingSubscription[0]['product_limit'] -
                    count($this->vproductsFactory->create()->getVendorProductIds($vendorId));
                if ($Remainingcount <= 0) {
                    unset($result['options'][$key]);
                    return $result;
                }
            }
        }
        return $result;
    }
}
