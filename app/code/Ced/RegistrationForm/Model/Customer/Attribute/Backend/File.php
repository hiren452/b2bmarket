<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\RegistrationForm\Model\Customer\Attribute\Backend;

use Magento\Catalog\Model\ImageUploader;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Catalog category image attribute backend model
 *
 * @api
 * @since 100.0.2
 */
class File extends AbstractBackend
{
    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var string
     */
    private $additionalData = '_additional_data_';

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param Http $request
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        Http $request,
        ImageUploader $imageUploader
    ) {
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_logger = $logger;
        $this->request = $request;
        $this->imageUploader = $imageUploader;
    }

    /**
     * Gets image name from $value array.
     * Will return empty string in a case when $value is not an array
     *
     * @param array $value Attribute value
     * @return string
     */
    private function getUploadedImageName($value)
    {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * Avoiding saving potential upload data to DB
     * Will set empty image attribute value if image was not uploaded
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $customer_params = $this->request->getParam('customer');
        $attributeName = $this->getAttribute()->getName();
        $value = [];
        if (isset($customer_params['documents'])) {
            $value = $customer_params['documents'];
            if ($imageName = $this->getUploadedImageName($value)) {
                $object->setData($this->additionalData . $attributeName, $value);
                $object->setData($attributeName, $imageName);
            }
        } elseif (!empty($customer_params)) {
            $object->setData($attributeName, null);
        }
        return parent::beforeSave($object);
    }

    /**
     * Save uploaded file and set its name to category
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->additionalData . $this->getAttribute()->getName());

        if ($imageName = $this->getUploadedImageName($value)) {
            try {
                $this->imageUploader->moveFileFromTmp($imageName);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
        return $this;
    }
}
