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
use Psr\Log\LoggerInterface;

class AdminhtmlRegisterObserver implements ObserverInterface
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
     * AdminhtmlRegisterObserver constructor.
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
        $param = $observer->getRequest();
        $customerData = $observer->getCustomer();
        $customerId = $customerData->getId();
        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $attributeCollection = $this->attributeCollectionFactory->create()
            ->setEntityTypeFilter($typeId)
            ->addVisibleFilter()
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');

        $customData = [];
        if (count($attributeCollection)) {
            foreach ($attributeCollection as $attribute) {
                if ($attribute->getFrontendInput() == 'file' || $attribute->getFrontendInput() == 'image') {
                    $uploadDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('ced/' . $customerId);
                    $image = $this->uploadFile($attribute->getAttributeCode(), $uploadDirectory . '/', $param->getPostValue());
                    if ($image != '') {
                        $path = 'ced/' . $customerId;
                        $customData[$attribute->getAttributeCode()] = $path . $image;
                    }
                }
            }
        }

        $customerData = $this->customerRepository->getById($customerId);
        $data = $this->customers->toFlatArray($customerData);

        $customer = $this->customerDataFactory->create();
        $customData = array_merge($this->customers->toFlatArray($customerData), $customData);
        $customData['id'] = $customerId;
        $this->dataObjectHelper->populateWithArray($customer, $customData, '\Magento\Customer\Api\Data\CustomerInterface');
        $this->customerRepository->save($customer);
    }

    /**
     * Upload file and get name.
     *
     * @param string $input
     * @param string $destination
     * @param array $data
     * @return string
     */
    public function uploadFile($input, $destination, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
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
            return '';
        }
        return '';
    }
}
