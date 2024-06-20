<?php

namespace Matrix\NoncatalogueRfq\Controller\Uploader;

use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Ajax extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory $fileUploader
     */
    protected $fileUploader;

    private $logger;

    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
        \Magento\Store\Model\StoreManagerInterface  $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->messageManager       = $messageManager;
        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;
        $this->logger               = $logger;
        $this->storeManager         = $storeManager;

        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $error = false;

        //$uploadedFile = $this->uploadFile($_FILES['files']['name']);

        $resultdata = $this->uploadFile();
        //$error  = (is_array($resultdata) && isset($resultdata['file'])) ? true:false;
        //$result = ['error'=>$error,'success'=> ($error) ? false : true,'data'=>$resultdata ];
        try {
            return $this->jsonResponse($resultdata);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    public function uploadFile()
    {
        $this->logger->info("uploadFile called");
        // this folder will be created inside "pub/media" folder
        $yourFolderName = 'rfq-noncataog-uploads/';

        // "my_custom_file" is the HTML input file name
        //$yourInputFileName = $fileName;
        $yourInputFileName = 'noncatrfqimage';

        try {
            $file = $this->getRequest()->getFiles($yourInputFileName);

            $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;

            if ($file && $fileName) {

                $this->logger->info("fileName=" . $fileName);
                $target = $this->mediaDirectory->getAbsolutePath($yourFolderName);
                list($fname, $fileExtension) =  explode(".", $fileName);
                $this->logger->info("fileExtension=" . $fileExtension);
                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                $uploader = $this->fileUploader->create(['fileId' => $yourInputFileName]);

                // set allowed file extensions
                $uploader->setAllowedExtensions(['jpg','jpeg', 'pdf', 'doc','docx', 'png', 'zip']);

                // allow folder creation
                $uploader->setAllowCreateFolders(true);

                // rename file name if already exists
                $uploader->setAllowRenameFiles(true);

                // rename the file name into lowercase
                // but this one is not working
                // we can simply use strtolower() function to rename filename to lowercase
                // $uploader->setFilenamesCaseSensitivity(true);

                // enabling file dispersion will
                // rename the file name into lowercase
                // and create nested folders inside the upload directory based on the file name
                // for example, if uploaded file name is IMG_123.jpg then file will be uploaded in
                // pub/media/your-upload-directory/i/m/img_123.jpg
                // $uploader->setFilesDispersion(true);

                // upload file in the specified folder
                $result = $uploader->save($target);
                $this->logger->info("Upload Result", $result);

                if ($result['file']) {
                    $this->messageManager->addSuccess(__('File has been successfully uploaded.'));
                }

                //return $target . $uploader->getUploadedFileName();
                $result['url'] = $this->getMediaUrl() . $yourFolderName . $uploader->getUploadedFileName();
                if (strtolower($fileExtension)=='pdf') {
                    $result['url'] = $this->getMediaUrl() . $yourFolderName . 'pdf-thumb.png';
                    $result['previewType'] ='image';
                }if (strtolower($fileExtension)=='doc' || strtolower($fileExtension)=='docx') {
                    $result['url'] = $this->getMediaUrl() . $yourFolderName . 'doc.png';
                    $result['previewType'] ='image';
                }
                $this->logger->info("uploadFile Result", $result);
                return $result;

            }
        } catch (\Exception $e) {
            $this->logger->info("Upload Err   " . $e->getMessage());
            $this->messageManager->addError($e->getMessage());
        }

        return false;
    }

    public function getMediaUrl()
    {
        $currentStore = $this->storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
