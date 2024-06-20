<?php

namespace Matrix\NoncatalogueRfq\Controller\Uploader;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Index extends \Magento\Framework\App\Action\Action
{
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

    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader
    ) {
        $this->messageManager       = $messageManager;
        $this->filesystem           = $filesystem;
        $this->fileUploader         = $fileUploader;

        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        parent::__construct($context);
    }

    public function execute()
    {
        // your code

        $uploadedFile = $this->uploadFile();

        // your code
    }

    public function uploadFile()
    {
        // this folder will be created inside "pub/media" folder
        $yourFolderName = 'rfq-noncataog-uploads/';

        // "my_custom_file" is the HTML input file name
        $yourInputFileName = 'noncatalogrfqupload';

        try {
            $file = $this->getRequest()->getFiles($yourInputFileName);
            $fileName = ($file && array_key_exists('name', $file)) ? $file['name'] : null;

            if ($file && $fileName) {
                $target = $this->mediaDirectory->getAbsolutePath($yourFolderName);

                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                $uploader = $this->fileUploader->create(['fileId' => $yourInputFileName]);

                // set allowed file extensions
                $uploader->setAllowedExtensions(['jpg', 'pdf', 'doc', 'png', 'zip']);

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

                if ($result['file']) {
                    $this->messageManager->addSuccess(__('File has been successfully uploaded.'));
                }

                return $target . $uploader->getUploadedFileName();
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return false;
    }
}
