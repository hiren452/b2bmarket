<?php

namespace Ced\RegistrationForm\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Customer\Mapper;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Eav\Model\Entity;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class FrontendRegisterObserver implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Entity
     */
    protected $eavEntity;

    /**
     * @var Mapper
     */
    protected $customers;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageAdapterFactory;

    /**
     * FrontendRegisterObserver constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param Entity $eavEntity
     * @param Mapper $customers
     * @param CustomerRepositoryInterface $customerRepository
     * @param Filesystem $fileSystem
     * @param UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        AttributeCollectionFactory $attributeCollectionFactory,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        Entity $eavEntity,
        Mapper $customers,
        CustomerRepositoryInterface $customerRepository,
        Filesystem $fileSystem,
        UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $imageAdapterFactory
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->eavEntity = $eavEntity;
        $this->customers = $customers;
        $this->customerRepository = $customerRepository;
        $this->fileSystem = $fileSystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
    }

    /**
     * Customer register event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getData('account_controller');
        $params = $data->getRequest();

        $customerData = $observer->getCustomer();
        $customerId = $customerData->getId();
        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $collection = $this->attributeCollectionFactory->create()
            ->setEntityTypeFilter($typeId)
            ->addVisibleFilter()
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        $customData = $params->getPostValue();
        if (count($collection)) {
            foreach ($collection as $attribute) {
                if ($attribute->getFrontendInput() == 'file' || $attribute->getFrontendInput() == 'image') {
                    $uploadDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('/customer/ced/' . $customerId);
                    $value = $this->uploadFile($attribute->getAttributeCode(), $uploadDir . '/', $params->getPostValue());
                    $path = '/ced/' . $customerId;
                    if (!$value) {
                        continue;
                    }
                    $customData[$attribute->getAttributeCode()] = $path . $value;
                }
            }
        }
        $savedCustomerData = $this->customerRepository->getById($customerId);
        $customer = $this->customerDataFactory->create();
        $customData = array_merge(
            $this->customers->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $this->customerRepository->save($customer);
    }

    /**
     * Upload file and get name.
     *
     * @param string $input
     * @param string $destination
     * @param array $data
     * @return string|false
     */
    public function uploadFile($input, $destination, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                mkdir($destination, 0755, true);
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $imageAdapter = $this->imageAdapterFactory->create();
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destination);
                return $result['file'];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }
}
