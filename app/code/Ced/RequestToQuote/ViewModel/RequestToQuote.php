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
 * @category    Ced
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class RequestToQuote implements ArgumentInterface
{
    const QUOTE_CREATE_EMAIL = "requesttoquote_configuration/email/quote_create_email";
    const RFQ_PO_CREATION_EMAIL = "requesttoquote_configuration/email/ced_requesttoquote_customer_po_creation";
    const RFQ_QUOTE_CREATION_EMAIL = "requesttoquote_configuration/email/ced_requesttoquote_customer_quote_creation";
    const QUOTE_UPDATE_EMAIL = "requesttoquote_configuration/email/quote_update_email_template";
    const RFQ_ADMIN_PO_CREATION_EMAIL = "requesttoquote_configuration/email/admin_ced_requesttoquote_customer_po_creation";
    const ADMIN_QUOTE_UPDATE_EMAIL = "requesttoquote_configuration/email/admin_quote_update_email_template";
    const ADMIN_QUOTE_COMPLETE_EMAIL = "requesttoquote_configuration/email/admin_quote_complete_email_template";
    const ADMIN_SEND_EMAIL = "requesttoquote_configuration/email/admin_quote_submit_email_template";
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var array
     */
    private $allowedProductTypes = ['simple', 'configurable', 'virtual'];

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $typeConfigurable;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface     $urlBuilder,
        \Magento\Catalog\Helper\Output $catalogHelperOutput
    ) {
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_customerSession = $customerSession;
        $this->date = $date;
        $this->productFactory = $productFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->customerFactory = $customerFactory;
        $this->typeConfigurable = $typeConfigurable;
        $this->moduleManager = $moduleManager;
        $this->_urlBuilder = $urlBuilder;
        $this->catalogHelperOutput = $catalogHelperOutput;
    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        return $this->moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getLoginToSeePriceHtml()
    {
        $html = '';
        if ($this->isEnable()) {
            if ($this->_customerSession->isLoggedIn()) {
                if ($this->getConfigValue('requesttoquote_configuration/active/hidepriceandcart')) {
                    if (in_array(
                        $this->_customerSession->getCustomerGroupId(),
                        $this->allowedCsutomerGroupsToAccessRFQ()
                    )) {
                        $html = '';
                    }
                }
            } elseif (!$this->getConfigValue('requesttoquote_configuration/active/hideguestcart')) {
                $html = $this->getSeePriceHtml();
            }
        }
        return $html;
    }

    /**
     * @return string
     */
    public function getSeePriceHtml()
    {
        $html = '<div class="price">';
        $html .= '<a href="' . $this->_urlBuilder->getUrl('customer/account/login') . '">';
        $html .= $this->getSeePriceMessage();
        $html .= '</a>';
        $html .= '</div>';
        return $html;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getSeePriceMessage()
    {
        $message = __('Login To See The Price');
        if ($pricemsg = $this->getConfigValue('requesttoquote_configuration/active/pricemsg')) {
            $message = $pricemsg;
        }
        return $message;
    }

    /**
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return $this->allowedProductTypes;
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getConfigValue('requesttoquote_configuration/active/enable');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isShowAddToCartAndPrice($productType = '')
    {
        $isShowAddToCart = true;
        if ($this->isEnable()) {
            if ($productType && !in_array($productType, $this->getAllowedProductTypes())) {
                return true;
            }
            if ($this->_customerSession->isLoggedIn()) {
                if ($this->getConfigValue('requesttoquote_configuration/active/hidepriceandcart')) {
                    $allowedCustomerGroups = $this->allowedCsutomerGroupsToAccessRFQ();
                    if (in_array($this->_customerSession->getCustomer()->getGroupId(), $allowedCustomerGroups)) {
                        $isShowAddToCart = false;
                    }
                }
            } elseif (!$this->getConfigValue('requesttoquote_configuration/active/hideguestcart')) {
                $isShowAddToCart = false;
            }
        }
        return $isShowAddToCart;
    }

    /**
     * @return array
     */
    public function allowedCsutomerGroupsToAccessRFQ()
    {
        $configValue = $this->getConfigValue('requesttoquote_configuration/active/custgroups');
        $customergroups = explode(',', $configValue);
        return $customergroups;
    }

    /**
     * @param $customer_id
     * @param $quote_id
     * @param $po_id
     * @param $link
     * @param $po_qty
     * @param $po_price
     * @param $cancel
     * @return \Magento\Framework\Phrase
     */
    public function sendPoCreatedMail(
        $quote_id,
        $po_id,
        $link,
        $customerName,
        $customerEmail,
        $storeId
    ) {
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        $email = $customerEmail;
        $emailvariables['customername'] = $customerName;
        $emailvariables['po_id'] = $po_id;
        $emailvariables['quote_id'] = $quote_id;
        $emailvariables['link'] = $link;
        $this->_template = $this->getConfigValue(self::RFQ_PO_CREATION_EMAIL);
        $template = $this->getConfigValue(self::RFQ_ADMIN_PO_CREATION_EMAIL);
        $this->_inlineTranslation->suspend();
        try {
            $this->_transportBuilder->setTemplateIdentifier($this->_template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars($emailvariables)
                ->setFrom($sender)
                ->addTo($email, $emailvariables['customername']);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->sendPoCreatedMailToAdmin($template, $emailvariables, $email);

        } catch (\Exception $e) {
        }
        $this->_inlineTranslation->resume();
        return true;
    }

    /**
     * @param $customer_id
     * @param $customer_mail
     * @param $quote_id
     * @param $link
     * @return \Magento\Framework\Phrase
     */
    public function sendQuoteCreatedMail($customer_id, $customer_mail, $quote_id, $link)
    {
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        $modeldata = $this->customerFactory->create()->load($customer_id);
        $emailvariables['customername'] = $modeldata->getFirstname();
        $emailvariables['quote_id'] = $quote_id;
        $emailvariables['link'] = $link;
        $emailvariables['subject'] = "A new Quote has been created by you.";
        $this->_template = $this->helper->getConfigValue(Data::RFQ_QUOTE_CREATION_EMAIL);
        $this->_inlineTranslation->suspend();
        try {
            $this->_transportBuilder->setTemplateIdentifier($this->_template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailvariables)
                ->setFrom($sender)
                ->addTo($customer_mail, $emailvariables['customername']);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
        }
        $this->_inlineTranslation->resume();
        return true;
    }

    /**
     * @param $template
     * @param array $tempate_variables
     * @param $reciever_email
     * @return \Magento\Framework\Phrase
     */
    public function sendEmail($template, $reciever_email, $tempate_variables = [])
    {
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        try {
            $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId()]
                )
                ->setTemplateVars($tempate_variables)
                ->setFrom($sender, $this->_storeManager->getStore()->getId())
                ->addTo($reciever_email)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
        }
        return true;
    }

    /**
     * @param $template
     * @param $tempate_variables
     * @param $customer_email
     * @return \Magento\Framework\Phrase
     */
    public function sendAdminEmail($adminTemplate, $tempate_variables, $customer_email)
    {
        $template = $adminTemplate;
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        $reciever_email = $this->getConfigValue('requesttoquote_configuration/active/admin_mail');
        if ($reciever_email) {
            try {
                $tempate_variables['customer_email'] = $customer_email;
                $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                    ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId()])
                    ->setTemplateVars($tempate_variables)
                    ->setFrom($sender)
                    ->addTo($reciever_email)
                    ->getTransport();
                $transport->sendMessage();
            } catch(\Exception $e) {
            }
        }
        return true;
    }

    /**
     * @param $template
     * @param $tempate_variables
     * @param $vendor_template_variables
     * @return \Magento\Framework\Phrase
     */
    public function sendVendorEmail($adminTemplate, $tempate_variables, $vendor_template_variables)
    {
        $template = $adminTemplate;
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        $reciever_email = $vendor_template_variables['vendor_email'];
        if ($reciever_email) {
            try {
                $tempate_variables['customer_email'] = $vendor_template_variables['customer_email'];
                $transport = $this->_transportBuilder->setTemplateIdentifier($template)
                    ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId()])
                    ->setTemplateVars($tempate_variables)
                    ->setFrom($sender)
                    ->addTo($reciever_email)
                    ->getTransport();
                $transport->sendMessage();
            } catch(\Exception $e) {
            }
        }
        return true;
    }

    /**
     * @param $template
     * @param $emailvariables
     * @param $customer_email
     * @return \Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendPoCreatedMailToAdmin($template, $emailvariables, $customer_email)
    {
        $store = $this->_storeManager->getStore();
        $sender = $this->getConfigValue('requesttoquote_configuration/active/email_identity');
        $reciever_email = $this->getConfigValue('requesttoquote_configuration/active/admin_mail');
        if ($reciever_email) {
            $emailvariables['customer_email'] = $customer_email;
            try {
                $this->_transportBuilder->setTemplateIdentifier($template)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )
                    ->setTemplateVars($emailvariables)
                    ->setFrom($sender)
                    ->addTo($reciever_email);
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
            } catch(\Exception $e) {
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getAdminNameInChat()
    {
        $adminName = $this->getConfigValue('requesttoquote_configuration/active/admin_name_in_chat');
        return $adminName;
    }
}
