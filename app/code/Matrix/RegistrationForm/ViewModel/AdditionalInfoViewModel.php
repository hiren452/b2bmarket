<?php

namespace Matrix\RegistrationForm\ViewModel;

use B2bmarkets\Custom\Helper\Data;
use Ced\RegistrationForm\Model\AttributeFactory;
use Ced\RegistrationForm\Model\ResourceModel\Attribute;
use Ced\RegistrationForm\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AdditionalInfoViewModel implements ArgumentInterface
{
    protected $attributeFactory;

    protected $configFactory;

    protected $helper;

    protected $attribute;

    protected $collectionFactory;

    protected $session;

    protected $customerRepository;

    protected $addressRepository;

    protected $extraDetails;
    private $_extraAttribute;

    /**
     * AdditionalInfoViewModel constructor.
     * @param AttributeFactory $attributeFactory
     * @param Attribute $attribute
     * @param ConfigFactory $configFactory
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        Attribute $attribute,
        ConfigFactory $configFactory,
        CollectionFactory $collectionFactory,
        Data $helper,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->configFactory = $configFactory;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->attribute = $attribute;
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * This function gets the attribute value.
     *
     * @param string|object|array $value
     * @param string|object|array $field
     * @return array|mixed|null
     */
    public function getAttributeVaue($value, $field)
    {
        if (!isset($this->_extraAttribute[$value])) {
            $modelFactory = $this->attributeFactory->create();
            $this->attribute->load($modelFactory, $value, $field);
            $this->_extraAttribute[$value] = $modelFactory->getData();
        }
        return $this->_extraAttribute[$value];
    }

    /**
     * This function filters the attribute value.
     *
     * @return array|null
     */
    public function getFilterAttributeVaue()
    {
        $collectionFactory = $this->collectionFactory->create();
        return $collectionFactory->addFieldToFilter('values', 'yes,no')->getData();
    }

    /**
     * This function gets the config from the code.
     *
     * @param string|object|array $code
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|null
     * @throws LocalizedException
     */
    public function getConfigFactory($code)
    {
        $configFactory = $this->configFactory->create();
        return $configFactory->getAttribute('customer', $code);
    }

    /**
     * Get attribute option list
     *
     * @param string $code
     * @return array
     * @throws LocalizedException
     */
    public function getAttributeOptions($code)
    {
        if ($code == 'buyer_industry') {
            $options = $this->getIndustries();
            foreach ($options as $key => &$option) {
                $option = [
                    'label' => $option,
                    'value' => $key,
                ];
            }
        } else {
            $configFactory = $this->configFactory->create();
            $attribute = $configFactory->getAttribute('customer', $code);
            $options = $attribute->getSource()->getAllOptions();
        }
        return $options;
    }

    /**
     * This function gets the industries.
     *
     * @return array
     */
    public function getIndustries()
    {
        return $this->helper->getIndustries();
    }

    /**
     * This function gets the company type data.
     *
     * @return string|null
     */
    public function getCompany()
    {
        $this->extraDetails = $this->getDetails();
        if ($this->extraDetails) {
            return $this->extraDetails->getCompany();
        }
        return null;
    }

    /**
     * This function gets the customer address details.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDetails()
    {
        $shippingAddressId = $this->session->getCustomer()->getDefaultShipping();
        try {
            if ($shippingAddressId) {
                $shippingAddress = $this->addressRepository->getById($shippingAddressId);
                return $shippingAddress;
            }
            return null;
        } catch (\Exception $e) {
            //            throw new LocalizedException(__($e->getMessage()));
            return null;
        }
    }

    /**
     * This function gets the customer telephone.
     *
     * @return null
     */
    public function getTelephone()
    {
        if ($this->extraDetails) {
            return $this->extraDetails->getTelephone();
        }
        return null;
    }

    /**
     * This function checks if the country is US or not.
     *
     * @param object|array|string $dataAttribute
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkIsCountryUs($dataAttribute)
    {
        if (in_array($dataAttribute, ['buyer_wholesale_seller_permit', 'buyer_employee_id_letter',
            'buyer_registration_document_upload', 'buyer_company_code'])) {
            $countryCode = "";
            if ($details = $this->getDetails()) {
                $countryCode = $details->getCountryId();
            }
            $codesAllowedForUS = ['buyer_wholesale_seller_permit', 'buyer_employee_id_letter', 'buyer_company_code'];
            $codeNotAllowedForUS = ['buyer_registration_document_upload'];
            if ($countryCode === 'US') {
                return in_array($dataAttribute, $codesAllowedForUS);
            }
            if ($countryCode != 'US') {
                return in_array($dataAttribute, $codeNotAllowedForUS);
            }
        }
        return true;
    }

    /**
     * This function checks if the country is US or not from the default shipping address.
     *
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCountryUSOrNot()
    {
        $details = $this->getDetails();
        $countryCode = $details ? $details->getCountryId() : null;
        return ($countryCode === "US" || $countryCode === null);
    }

    /**
     * This function returns the company field based on the default shipping address is available or not.
     *
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function disableCompanyTelephoneField()
    {
        $customer = $this->customerRepository->getById($this->session->getCustomer()->getId());
        $shippingAddressId = $customer->getDefaultShipping();
        if ($shippingAddressId == null) {
            return 'disabled';
        }
        return '';
    }
}
