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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Controller\Rewrite\Account;

use Ced\CsMarketplace\Helper\Data;
use Ced\CsMarketplace\Model\Account\Redirect as AccountRedirect;
use Ced\CsMarketplace\Model\Url as CustomerUrl;
use Ced\CsMarketplace\Model\Vendor;
use Ced\CsMarketplace\Model\VendorFactory;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class CreatePost extends \Ced\CsMarketplace\Controller\Account\CreatePost
{

    /**
     *
     *
     * @var AccountManagementInterface
     */
    protected $vendorAcManagement;

    /**
     *
     * @var CsAddressHelper
     */
    protected $csAddressHelper;

    /**
     *
     * @var CsFormFactory
     */
    protected $csFormFactory;

    /**
     *
     * @var CsSubscriberFactory
     */
    protected $csSubscriberFactory;

    /**
     *
     * @var CsRegionDataFactory
     */
    protected $csRegionDataFactory;

    /**
     *
     * @var CsAddressDataFactory
     */
    protected $csAddressDataFactory;

    /**
     *
     * @var vendorRegistration
     */
    protected $vendorRegistration;

    /**
     *
     * @var customerDataFactory
     */
    protected $customerDataFactory;

    /**
     *
     * @var VendorUrl
     */
    protected $vendorUrl;

    /**
     *
     * @var CsEscaper
     */
    protected $csEscaper;

    /**
     *
     * @var CustomerExtractor
     */
    protected $customerExtractor;

    /**
     *
     * @var VendorUrlModel
     */
    protected $vendorUrlModel;

    /**
     *
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     *
     * @var Session
     */
    protected $vendorSession;

    /**
     *
     * @var AccountRedirect
     */
    protected $accountRedirect;

    protected $multistepHelper;

    protected $vendorCollection;

    /**
     * @var Timezone
     */
    protected $timezone;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $vendorAcManagement
     * @param Address $csAddressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $csFormFactory
     * @param SubscriberFactory $csSubscriberFactory
     * @param RegionInterfaceFactory $csRegionDataFactory
     * @param AddressInterfaceFactory $csAddressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $vendorUrl
     * @param Registration $vendorRegistration
     * @param Escaper $csEscaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param VendorFactory $Vendor
     * @param Data $datahelper
     * @param CaptchaHelper $captchaHelper
     * @param CaptchaStringResolver $captchaStringResolver
     * @param \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollection
     * @param Vendor $vendor
     * @param \Ced\CsMultistepreg\Helper\Data $multistepHelper
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $vendorAcManagement,
        Address $csAddressHelper,
        UrlFactory $urlFactory,
        FormFactory $csFormFactory,
        SubscriberFactory $csSubscriberFactory,
        RegionInterfaceFactory $csRegionDataFactory,
        AddressInterfaceFactory $csAddressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $vendorUrl,
        Registration $vendorRegistration,
        Escaper $csEscaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        VendorFactory $Vendor,
        Data $datahelper,
        CaptchaHelper $captchaHelper,
        CaptchaStringResolver $captchaStringResolver,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollection,
        Vendor $vendor,
        \Ced\CsMultistepreg\Helper\Data $multistepHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->vendorSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->vendorAcManagement = $vendorAcManagement;
        $this->csAddressHelper = $csAddressHelper;
        $this->csFormFactory = $csFormFactory;
        $this->csSubscriberFactory = $csSubscriberFactory;
        $this->csRegionDataFactory = $csRegionDataFactory;
        $this->csAddressDataFactory = $csAddressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->vendorUrl = $vendorUrl;
        $this->vendorRegistration = $vendorRegistration;
        $this->csEscaper = $csEscaper;
        $this->customerExtractor = $customerExtractor;
        $this->vendorUrlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;
        $this->vendorCollection = $vendorCollection;
        $this->vendor = $vendor;
        $this->dataHelper = $datahelper;
        $this->multistepHelper = $multistepHelper;
        $this->captchaHelper = $captchaHelper;
        $this->captchaStringResolver = $captchaStringResolver;
        $this->timezone = $timezone;
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $vendorAcManagement,
            $csAddressHelper,
            $urlFactory,
            $csFormFactory,
            $csSubscriberFactory,
            $csRegionDataFactory,
            $csAddressDataFactory,
            $customerDataFactory,
            $vendorUrl,
            $vendorRegistration,
            $csEscaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $Vendor,
            $datahelper,
            $captchaHelper,
            $captchaStringResolver,
            $timezone
        );
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }

        $addressForm = $this->csFormFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $vendorAddressData = [];

        $regionDataObject = $this->csRegionDataFactory->create();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $vendorAddressData[$attributeCode] = $value;
            }
        }
        $csAddressDataObject = $this->csAddressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $csAddressDataObject,
            $vendorAddressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $csAddressDataObject->setRegion($regionDataObject);

        $csAddressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $csAddressDataObject;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->vendorSession->isLoggedIn() || !$this->vendorRegistration->isAllowed()) {
            $resultRedirect->setPath('csmarketplace/vendor/index');
            return $resultRedirect;
        }

        if (!$this->getRequest()->isPost()) {
            $vendorUrl = $this->vendorUrlModel->getUrl('*/*/login', ['_secure' => true, 'create' => true]);
            $resultRedirect->setUrl($this->_redirect->error($vendorUrl));
            return $resultRedirect;
        }

        $this->vendorSession->regenerateId();
        $venderData = $this->getRequest()->getParam('vendor');
        $venderData['shop_url'] = strtolower($venderData['shop_url']);
        $vData = $this->vendorCollection->create()->addAttributeToFilter('shop_url', $venderData['shop_url'])->getData();
        if (sizeof($vData) > 0) {
            $this->messageManager->addError(__('Shop url already exist. Please Provide another Shop Url'));
            $vendorUrl = $this->vendorUrlModel->getUrl('*/*/login', ['_secure' => true, 'create' => true]);
            $resultRedirect->setUrl($this->_redirect->error($vendorUrl));
            return $resultRedirect;
        }
        try {
            $vendorAddress = $this->extractAddress();
            $addresses = $vendorAddress === null ? [] : [$vendorAddress];

            $vendor = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $vendor->setAddresses($addresses);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->vendorSession->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);

            $vendor = $this->vendorAcManagement->createAccount($vendor, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->csSubscriberFactory->create()->subscribeCustomerById($vendor->getId());
            }

            if ($this->getRequest()->getParam('is_vendor') == 1) {

                $customerData = $vendor;

                try {

                    $vendordata = $this->vendor
                        ->setCustomer($customerData)
                        ->register($venderData);

                    if (!$vendordata->getErrors()) {
                        $vendordata->save();

                    } elseif ($vendordata->getErrors()) {
                        foreach ($vendordata->getErrors() as $error) {
                            $this->_session->addError($error);
                        }
                        $this->_session->setFormData($venderData);
                    } else {
                        $this->_session->addError(__('Your vendor application has been denied'));
                    }
                } catch (\Exception $e) {
                    $this->dataHelper->logException($e);
                }
            }
            $enabled = $this->multistepHelper->isEnabled();
            if ($enabled != '1') {
                $confirmationStatus = $this->vendorAcManagement->getConfirmationStatus($vendor->getId());
                if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                    $email = $this->vendorUrl->getEmailConfirmationUrl($vendor->getEmail());
                    $this->messageManager->addComplexSuccessMessage('addCustomSuccessMessage', [
                            'html' => 'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                            'params' => $email
                        ]);
                    $vendorUrl = $this->vendorUrlModel->getUrl('*/*/login', ['_secure' => true]);
                    $resultRedirect->setUrl($this->_redirect->success($vendorUrl));
                } else {
                    $this->vendorSession->setCustomerDataAsLoggedIn($vendor);
                    $this->messageManager->addSuccessMessage($this->getSuccessMessage());
                    $resultRedirect = $this->accountRedirect->getRedirect();
                }
            }

            $vendorId = $vendordata->getEntityId();
            if ($enabled == '1') {
                $this->vendorSession->setCustomerDataAsLoggedIn($vendor);
                $resultRedirect->setPath('csmultistep/multistep/index', ['id' => $vendorId]);
                return $resultRedirect;
            }
            return $resultRedirect;
        } catch (StateException $e) {
            $forgotPassUrl = $this->vendorUrlModel->getUrl('customer/account/forgotpassword');
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $forgotPassUrl
            );
            $this->messageManager->addError($message);
        } catch (InputException $e) {
            $this->messageManager->addError($this->csEscaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($this->csEscaper->escapeHtml($error->getMessage()));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t save the vendor.'));
        }

        $this->vendorSession->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->vendorUrlModel->getUrl('*/*/login', ['_secure' => true, 'create' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmationPass
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmationPass)
    {
        if ($password != $confirmationPass) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Retrieve success message if vendor created
     * @return array|\Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSuccessMessage()
    {
        if ($this->csAddressHelper->isVatValidationEnabled()) {
            if ($this->csAddressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                $successMessage = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your shipping address for proper VAT calculation.',
                    $this->vendorUrlModel->getUrl('customer/address/edit')
                );
            } else {
                $successMessage = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->vendorUrlModel->getUrl('customer/address/edit')
                );
            }
        } else {
            $successMessage = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $successMessage;
    }
}
