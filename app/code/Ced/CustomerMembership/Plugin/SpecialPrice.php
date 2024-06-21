<?php
namespace Ced\CustomerMembership\Plugin;

class SpecialPrice
{

    /**
     * SpecialPrice constructor.
     */
    public function __construct(
        \Ced\CustomerMembership\Helper\Data $data
    ) {
        $this->helper=$data;
    }

    public function afterGetSpecialPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($this->helper->getModuleStatus()) {
            if (!$this->helper->subscriptionStatus()) {
                $result = "";
            }
        }
        return $result;
    }
}
