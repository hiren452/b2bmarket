<?php
namespace Matrix\NoncatalogueRfq\Ui\Component\Form\MembershipRfqFees;

use Magento\Framework\Data\OptionSourceInterface;

class MembershipplanOptions implements OptionSourceInterface
{
    /**
     * @var UomoptionsFactory
     */
    protected $_membershipFactory;

    public function __construct(
        \Ced\CustomerMembership\Model\ResourceModel\Membership\CollectionFactory  $membershipFactory
    ) {
        $this->_membershipFactory =  $membershipFactory;
    }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        $result = [];
        $collection = $this->_membershipFactory->create();
        if (count($collection)) {
            foreach ($collection as $customerMembership) {
                //$result[] = ['value' => $option['value'], 'label' => $option['label']];
                $result[$customerMembership->getId()] = $customerMembership->getPlanName();

            }
        }
        return $result;
    }
}
