<?php

namespace B2bmarkets\MultiStepReg\Controller\Index;

class Save extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;

    protected $customerRepositoryInterface;

    public $customerSession;

    protected $addressRepository;

    protected $addressDataFactory;

    protected $uploaderFactory;

    protected $mediaDirectory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->pageFactory           = $pageFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession    = $customerSession;
        $this->addressRepository  = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->uploaderFactory    = $uploaderFactory;
        $this->mediaDirectory       = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        return parent::__construct($context);
    }

    public function execute()
    {
        try {

            $data = $this->getRequest()->getParams();
            /* save custom attributes */
            $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomerId());

            $customer->setCustomAttribute('buyer_industry', $this->getRequest()->getParam('buyer_industry'));
            $customer->setCustomAttribute('buyer_company_type', $this->getRequest()->getParam('buyer_company_type'));
            $customer->setCustomAttribute('buyer_company_website', $this->getRequest()->getParam('buyer_company_website'));
            $customer->setCustomAttribute('buyer_company_code', $this->getRequest()->getParam('buyer_company_code'));
            $customer->setCustomAttribute('buyer_dun_bradstreet', $this->getRequest()->getParam('buyer_dun_bradstreet'));
            $customer->setCustomAttribute('buyer_public_name', $this->getRequest()->getParam('buyer_public_name'));

            $doc = $this->uploadFile();
            if (!empty($doc)) {
                $customer->setCustomAttribute('buyer_registration_document_upload', $doc);
            }

            $customer->setCustomAttribute('buyer_multi_step', 1);

            $this->customerRepositoryInterface->save($customer);
            /* save custom attributes */

            /* save address */
            $address = $this->addressDataFactory->create();

            $address->setPrefix($this->customerSession->getCustomer()->getPrefix());
            $address->setFirstname($this->customerSession->getCustomer()->getFirstname());
            $address->setLastname($this->customerSession->getCustomer()->getLastname());
            $address->setTelephone($this->getRequest()->getParam('telephone'));
            $address->setStreet($this->getRequest()->getParam('street'));
            $address->setCity($this->getRequest()->getParam('city'));
            $address->setCountryId($this->getRequest()->getParam('country_id'));
            $address->setPostcode($this->getRequest()->getParam('postcode'));
            $address->setRegionId($this->getRequest()->getParam('region_id'));
            $address->setCompany($this->getRequest()->getParam('company'));
            //$address->setRegion($data['company']);
            $address->setIsDefaultShipping(1);
            $address->setIsDefaultBilling(1);
            $address->setCustomerId($this->customerSession->getCustomerId());

            $this->addressRepository->save($address);
            /* save address */

        } catch (\Exception $e) {
            //$this->messageManager->addError($e->getMessage());
            $this->_redirect('multistepreg/index/index');
        }

        $this->_redirect('customer/account/edit');
    }

    public function uploadFile()
    {
        $custom_path = '/' . $this->customerSession->getCustomerId() . '/';
        // this folder will be created inside "pub/media" folder
        $yourFolderName = 'customer' . $custom_path;

        // "my_custom_file" is the HTML input file name
        $yourInputFileName = 'buyer_registration_document_upload';

        try {
            $file = $this->getRequest()->getFiles($yourInputFileName);
            $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;

            if ($file && $fileName) {
                $target = $this->mediaDirectory->getAbsolutePath($yourFolderName);

                /**
 * @var $uploader \Magento\MediaStorage\Model\File\Uploader
*/
                $uploader = $this->uploaderFactory->create(['fileId' => $yourInputFileName]);

                // set allowed file extensions
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'docx', 'doc','excel','csv','txt']);

                // allow folder creation
                $uploader->setAllowCreateFolders(true);

                // rename file name if already exists
                $uploader->setAllowRenameFiles(true);
                $result = $uploader->save($target);

                if ($result['file']) {
                    //$this->messageManager->addSuccess(__('File has been successfully uploaded => '.$result['file']));
                    return $custom_path . $uploader->getUploadedFileName();
                }
            }
        } catch (\Exception $e) {
            //$this->messageManager->addError($e->getMessage());
        }

        return '';
    }
}
