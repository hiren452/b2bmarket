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
 * @package     Ced_CsRfq
 * @author        CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsRfq\Controller\Setting;

use Ced\CsMarketplace\Helper\Acl;
use Ced\CsMarketplace\Helper\Data as CsMarketplaceHelperData;
use Ced\CsMarketplace\Model\VendorFactory;
use Ced\CsMarketplace\Model\VsettingsFactory;
use Ced\CsRfq\Helper\Data as CsRfqHelperData;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Ced\CsMarketplace\Model\VsettingsFactory
     */
    protected $vsettingsFactory;

    /**
     * @param VsettingsFactory $vsettingsFactory
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param UrlFactory $urlFactory
     * @param Registry $registry
     * @param JsonFactory $jsonFactory
     * @param CsMarketplaceHelperData $csmarketplaceHelper
     * @param Acl $aclHelper
     * @param VendorFactory $vendor
     * @param CsRfqHelperData $csRfqHelper
     */
    public function __construct(
        VsettingsFactory $vsettingsFactory,
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        Registry $registry,
        JsonFactory $jsonFactory,
        CsMarketplaceHelperData $csmarketplaceHelper,
        Acl $aclHelper,
        VendorFactory $vendor,
        CsRfqHelperData $csRfqHelper,
        JsonSerializer $serializer
    ) {
        $this->vsettingsFactory = $vsettingsFactory;
        $this->csRfqHelper = $csRfqHelper;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (! $this->csRfqHelper->isVendorRfqEnable()) {
            $this->_redirect('csmarketplace/vendor');
            return;
        }

        if (!$this->_getSession()->getVendorId()) {
            return;
        }
        $section = $this->getRequest()->getParam('section', '');
        $groups = $this->getRequest()->getPost('groups', []);
        if (strlen($section) > 0 && $this->_getSession()->getData('vendor_id') && count($groups) > 0) {
            $vendor_id = (int)$this->_getSession()->getData('vendor_id');
            try {
                foreach ($groups as $code => $values) {
                    foreach ($values as $name => $value) {
                        $serialized = 0;
                        $key = strtolower($section . '/' . $code . '/' . $name);
                        if (is_array($value)) {
                            $value = $this->_serializer->serialize($value);
                            $serialized = 1;
                        }
                        $setting = false;
                        $setting = $this->vsettingsFactory->create()
                            ->loadByField(['key', 'vendor_id'], [$key, $vendor_id]);

                        if ($setting && $setting->getId()) {
                            $setting->setVendorId($vendor_id)
                                ->setGroup($section)
                                ->setKey($key)
                                ->setValue($value)
                                ->setSerialized($serialized)
                                ->save();
                        } else {

                            $setting = $this->vsettingsFactory->create();
                            $setting->setVendorId($vendor_id)
                                ->setGroup($section)
                                ->setKey($key)
                                ->setValue($value)
                                ->setSerialized($serialized)
                                ->save();
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(__('The setting information has been saved.'));
                $this->_redirect('*/*');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*');
                return;
            }
        }
        $this->_redirect('*/*');
    }
}
