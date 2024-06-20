<?php

namespace Ced\RegistrationForm\Plugin;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;

/**
 * Plugin for image block
 */
class CustomerDocuments
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerResource
     */
    protected $customerResource;

    /**
     * CustomerDocuments constructor.
     * @param CustomerFactory $customerFactory
     * @param CustomerResource $customerResource
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerResource $customerResource
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerResource = $customerResource;
    }

    /**
     * After getData plugin
     *
     * @param \Magento\Customer\Model\Customer\DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(\Magento\Customer\Model\Customer\DataProvider $subject, $result)
    {
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $customerData = $value;
                $customerId = isset($customerData['customer']['entity_id']) ? $customerData['customer']['entity_id'] : null;
                if ($customerId) {
                    $customer = $this->customerFactory->create();
                    $this->customerResource->load($customer, $customerId);
                    $customerDataArray = $customer->getData();

                    if (isset($customerDataArray['documents'])) {
                        unset($result[$key]['customer']['documents']);
                        $result[$key]['customer']['documents'][0]['name'] = $customerDataArray['documents'];
                        $result[$key]['customer']['documents'][0]['url'] = $customer->getCertificateUrl();
                    }
                }
            }
        }
        return $result;
    }
}
