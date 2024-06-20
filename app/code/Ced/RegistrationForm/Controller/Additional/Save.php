<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_RegistrationForm
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\RegistrationForm\Controller\Additional;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory as ImageAdapterFactory;

class Save extends \Magento\Customer\Controller\AbstractAccount
{

    protected $CustomerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagers;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_attrCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_eavEntity;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;
    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $fileSystem
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $CustomerSession
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $fileSystem,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $CustomerSession,
        UploaderFactory $uploaderFactory,
        ImageAdapterFactory $imageAdapterFactory,
        DirectoryList $directoryList
    ) {
        $this->_storeManagers = $storeManager;
        $this->fileSystem = $fileSystem;
        $this->_attrCollection = $attributeCollection;
        $this->CustomerSession = $CustomerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_eavEntity = $eavEntity;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $postvalue = $this->getRequest();
        $customerId = $this->CustomerSession->getCustomerId();
        $typeId = $this->_eavEntity->setType('customer')->getTypeId();
        $collections = $this->_attrCollection->create()
            ->setEntityTypeFilter($typeId)
            ->addVisibleFilter()
            ->addFilter('is_user_defined', 1)
            ->setOrder('sort_order', 'ASC');
        $customData = $postvalue->getPostValue();
        $savedCustomerData = $this->_customerRepository->getById($customerId);
        $saveData = $this->_customerMapper->toFlatArray($savedCustomerData);
        if (count($collections)) {
            foreach ($collections as $attribute) {
                if ($attribute->getFrontendInput() == 'file' || $attribute->getFrontendInput() == 'image') {
                    $uploadDir = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath('ced/' . $customerId);
                    $value = $this->uploadFile($attribute->getAttributeCode(), $uploadDir . '/', $postvalue->getPostValue());
                    $path = 'ced/' . $customerId;

                    $customData[$attribute->getAttributeCode()] = $path . $value;
                    if ($value == '') {
                        if (isset($saveData[$attribute->getAttributeCode()])) {
                            $customData[$attribute->getAttributeCode()] = $saveData[$attribute->getAttributeCode()];
                        }
                    }
                }
            }
        }

        $customer = $this->_customerDataFactory->create();
        $customData = array_merge(
            $this->_customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->_dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $this->_customerRepository->save($customer);
        return $resultRedirect->setPath('*/additional/');
    }
    /**
     * Upload file method.
     *
     * @param string $input
     * @param string $destinationFolder
     * @param array $data
     * @return string
     */
    public function uploadFile($input, $destinationFolder, $data)
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
                $result = $uploader->save($this->directoryList->getPath('media') . '/' . $destinationFolder);

                return $result['file'];
            }
        } catch (\Exception $e) {
            return '';
        }

        return '';
    }
}
