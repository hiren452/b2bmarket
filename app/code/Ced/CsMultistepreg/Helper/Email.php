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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultistepreg\Helper;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'ced_csmarketplace/multistepregistration/profilecompletionemail';

    protected $_scopeConfig;

    protected $_storeManager;

    protected $inlineTranslation;

    protected $_transportBuilder;

    protected $temp_id;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
    }

    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {

        $storeId  =   $this->_storeManager->getStore()->getId();
        $transportBuilder = $objectManager->get('Magento\Framework\Mail\Template\TransportBuilder');

        $template =  $transportBuilder->setTemplateIdentifier('ced_csmarketplace_multistepregistration_profilecompletionemail')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    public function sendEmail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        try {

            $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);
            $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
            $transportBuilder = $objectManager->get('Magento\Framework\Mail\Template\TransportBuilder');
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {

        }
    }
}
