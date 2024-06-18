<?php
namespace Matrix\NoncatalogRfqfrom\Controller\Index;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogRfqfrom\Helper\Config;
use Matrix\RfqEntity\Model\ResourceModel\Attribute\CollectionFactory;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
    *
    * @var Session
    */
    protected $session;

    /**
    *
    * @var UrlInterface
    */
    protected $urlInterface;

    /**
    *
    * @var Registry
    */
    protected $_coreRegistry;

    /**
    *
    * @var CollectionFactory
    */
    protected $_attributeCollection;

    /**
    *
    * @var CustomerFactory
    */
    protected $customerModelFactory;

    /**
    * @var RfqEntityFactory
    */
    protected $rfqentityFactory;

    /**
    * @var ManagerInterface
    */
    protected $messageManager;

    /**
    * @var Config
    */
    private $config;

    /**
    * @param Context $context
    * @param Session $customerSession
    * @param PageFactory $resultPageFactory
    * @param UrlInterface $urlInterface
    * @param CollectionFactory $attributeCollection
    * @param RfqEntityFactory $rfqentityFactory
    * @param ManagerInterface $messageManager
    * @param CustomerFactory $customerModelFactory
    * @param Config $config
    * @param Registry $coreRegistry
    */
    public function __construct(
        Context $context,
        Session $session,
        UrlInterface $urlInterface,
        PageFactory $pageFactory,
        CollectionFactory $attributeCollection,
        RfqEntityFactory $rfqentityFactory,
        ManagerInterface $messageManager,
        CustomerFactory $customerModelFactory,
        Config $config,
        Registry $coreRegistry
    ) {
        $this->resultPageFactory = $pageFactory;
        $this->session = $session;
        $this->customerModelFactory = $customerModelFactory;
        $this->urlInterface = $urlInterface;
        $this->_coreRegistry = $coreRegistry;
        $this->_attributeCollection = $attributeCollection;
        $this->rfqentityFactory = $rfqentityFactory;
        $this->messageManager = $messageManager;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $postData = $this->config->getAttributeData();
        if (!$postData) {
            $this->messageManager->addError('NO DATA END');
        }
        $postData = [
            "product_image" => "watchband.jpeg",
            "adhar_number" => "BFGYD1258965966969",
            "address_type" => "5479",
            "need_help" => "5481",
            "end_date" => "08/19/2020",
            "gsnt_number" => "GGT8896969"
        ];

        $model = $this->_attributeCollection
            ->addFieldToFilter('status', ['eq' => 1])
            ->getData();

        $rfqEntityData = [];

        foreach ($model as $k => $v) {
            $attribute_code = $v['attribute_code'];
            $this->getResponse()->appendBody($attribute_code . ",");
            if (array_key_exists($attribute_code, $postData) && isset($postData[$attribute_code])) {
                $this->getResponse()->appendBody("Attribute=" . $attribute_code . ", post Value=" . $postData[$attribute_code] . "<br />");
                $rfqEntityData[$attribute_code] = $postData[$attribute_code];
            }
        }

        if (count($rfqEntityData)) {
            $rfq_id = 101;
            $rfqEntityData['rfq_id'] = $rfq_id; // Set RFQ ID manually for better Mapping
            $this->createRfqEntity($rfqEntityData);
        }

        return $this->resultPageFactory->create();
    }

    private function getRfqEntity($entityId)
    {
        if (!isset($entityId) || $entityId <= 0) {
            return;
        }

        $rfqentityData = $this->rfqentityFactory->create()->load($entityId);
        if ($rfqentityData->getId()) {
            $this->getResponse()->appendBody("<h2>Non-catalog RFQ Entity Data</h2>");
        } else {
            $this->messageManager->addError("The Non-catalog RFQ Entity does not exist.");
        }
    }

    private function createRfqEntity($entityData)
    {
        try {
            if (!is_array($entityData) || count($entityData) <= 0) {
                return null;
            }

            $rfqentityData = $this->rfqentityFactory->create();
            foreach ($entityData as $attribCode => $value) {
                $rfqentityData->setData($attribCode, $value);
            }
            $rfqentityData->save();
            return $rfqentityData;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the Non-catalog RFQ Entity.'));
        }
    }

    private function updateRfqEntity($entityId)
    {
        try {
            if (!isset($entityId) || $entityId <= 0) {
                return;
            }

            $rfqentityData = $this->rfqentityFactory->create()->load($entityId);
            if ($rfqentityData->getId()) {
                $rfqentityData->setData('rfq_id', 106);
                $rfqentityData->save();
                $this->getResponse()->appendBody("The Non-catalog RFQ Entity successfully updated. <br />");
            } else {
                $this->messageManager->addError("The Non-catalog RFQ Entity does not exist.");
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while updating the Non-catalog RFQ Entity.'));
        }
    }
}
