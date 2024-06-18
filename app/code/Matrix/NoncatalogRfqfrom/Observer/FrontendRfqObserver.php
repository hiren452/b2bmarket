<?php
namespace Matrix\NoncatalogRfqfrom\Observer;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Matrix\NoncatalogRfqfrom\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Matrix\RfqEntity\Model\RfqEntityFactory;

class FrontendRfqObserver implements ObserverInterface
{
    const ENTITY_TYPE_ID = 10;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var RfqEntityFactory
     */
    protected $rfqEntityFactory;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * FrontendRfqObserver constructor.
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param RfqEntityFactory $rfqEntityFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Registry $registry
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        DataObjectHelper $dataObjectHelper,
        RfqEntityFactory $rfqEntityFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        Registry $registry,
        ManagerInterface $messageManager
    ) {
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->rfqEntityFactory = $rfqEntityFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->registry = $registry;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getData('noncatalogrequesttoquote_controller');
        $noncatalogRfq = $observer->getData('noncatalogRfq');
        $rfqId = $noncatalogRfq->getData('rfq_id');

        if (!isset($rfqId) || $rfqId <= 0) {
            return;
        }

        $params = $data->getRequest();
        $postData = $params->getPostValue();

        $collection = $this->attributeCollectionFactory->create()
            ->setEntityTypeFilter(self::ENTITY_TYPE_ID)
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');

        if ($collection->getSize() > 0) {
            $rfqEntityData = [];
            foreach ($collection as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $attributeFrontendInput = $attribute->getFrontendInput();

                if ($attributeFrontendInput == 'file' || $attributeFrontendInput == 'image') {
                    $uploadDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('rfqentity/');
                    $value = $this->uploadFile($attributeCode, $uploadDir, $params->getPostValue());
                    $path = 'rfqentity/';
                    $rfqEntityData[$attributeCode] = $path . $value;
                } else {
                    $rfqEntityData[$attributeCode] = $postData[$attributeCode];
                }
            }

            if (!empty($rfqEntityData)) {
                $rfqEntityData['rfq_id'] = $rfqId;
                $this->createRfqEntity($rfqEntityData);
            }
        }
    }

    private function createRfqEntity($entityData)
    {
        try {
            if (!is_array($entityData) || count($entityData) <= 0) {
                return null;
            }

            $rfqEntity = $this->rfqEntityFactory->create();
            $rfqEntity->addData($entityData);
            $rfqEntity->save();

            return $rfqEntity;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while saving the Non-catalog RFQ Entity.'));
        }
    }

    private function uploadFile($input, $destination, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            }

            $uploader = $this->uploaderFactory->create(['fileId' => $input]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($destination);

            return $result['file'];
        } catch (\Exception $e) {
            return '';
        }
    }
}
