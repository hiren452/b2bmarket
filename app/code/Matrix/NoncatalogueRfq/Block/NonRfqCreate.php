<?php
namespace Matrix\NoncatalogueRfq\Block;

use Ced\CsMarketplace\Model\Session;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Result\PageFactory;
use Matrix\NoncatalogueRfq\Helper\Data;

class NonRfqCreate extends \Magento\Framework\View\Element\Template
{
    const NONCAT_RFQ_ATTACHEMEMT_FOLDER = 'rfq-noncataog-uploads';
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var UrlFactory
     */
    protected $urlModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var vendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var RfqTemplateFactory
     */
    protected $_rfqtemplateFactory;

    /**
     * @var \Magento\Customer\Model\Customer|null
     */
    protected $_customerFactory;

    /**
     * @var null
     */
    protected $_vendorUrl;

    protected $helper;

    protected $json;

    private $objectFactory;

    private $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $filesystem;

    protected $_coreRegistry = null;

    protected $jsonHelper;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Context $context,
        Session $customerSession,
        \Matrix\NoncatalogueRfq\Model\Session $noncatrfqSession,
        UrlFactory $urlFactory,
        Data $helper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Filesystem $filesystem,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Matrix\NoncatalogueRfq\Model\RfqTemplateFactory  $rfqtemplateFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $objectFactory
    ) {
        $this->_customerFactory = $customerFactory;
        $this->urlModel = $urlFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->session = $customerSession->getCustomerSession();
        $this->noncatrfqsession = $noncatrfqSession;
        $this->_vendorFactory = $vendorFactory;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->_rfqtemplateFactory = $rfqtemplateFactory;
        $this->json = $json;
        $this->objectFactory = $objectFactory;
        $this->filesystem    = $filesystem;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
        $this->mediaDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        //$this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    public function getVendorCollection()
    {
        $collection = $this->_vendorFactory->create()->getCollection()
           ->addAttributeToSelect('public_name')
           ->addAttributeToSelect('name')
           ->addAttributeToSelect('email')
           ->addAttributeToSelect('group')
           ->addAttributeToSelect('status')
           ->addAttributeToFilter('status', ['eq'=>'approved']);
        // echo $collection->getSelect();

        return $collection;
    }

    public function getRfqPopupFormData()
    {
        $rfqpoupForm =  null;
        $jsonPopformData = $this->_coreRegistry->registry('matrix_rfqpopupfrom');
        if (isset($jsonPopformData) && $jsonPopformData!='') {
            $rfqpoupForm = $this->jsonHelper->jsonDecode($jsonPopformData);
        }
        return $rfqpoupForm;
    }
    public function getCurrentRfqTemplate()
    {
        if (!$this->hasData('matrix_currentrfqtemplate')) {
            $this->setData('matrix_currentrfqtemplate', $this->_coreRegistry->registry('matrix_currentrfqtemplate'));
        }

        $customerId = $this->session->getCustomer()->getId();
        if (!isset($customerId)) {
            return null;
        }

        $obj = $this->objectFactory->create();
        /*$unsavedRfq_model = $this->_rfqtemplateFactory->create();
        $unsavedRfq_model->load($customerId,'customer_id');*/
        $unsavedRfq_model = $this->_coreRegistry->registry('matrix_currentrfqtemplate');
        if (isset($unsavedRfq_model)) {
            $result = $unsavedRfq_model->getData('form_data');
            $data = $this->json->unserialize($result);
            if (array_key_exists('uploads', $data)) {
                $data['uploadattachement'] =    $this->populateAttachements($data['uploads']);
            }
            $obj->setData('rfqTemplate', $data);
            $obj->setData('template_id', $unsavedRfq_model->getId());
        } else {
            $obj->setData('rfqTemplate', []);
        }
        return $obj;
    }

    public function getVendorJson()
    {
        $collection = $this->getVendorCollection();
        $arrOptions = [];
        foreach ($collection as $item) {
            $arrOptions[] =  [
            "value" => $item->getData('entity_id'),
            "label" => $item->getData('public_name')
            ];
        }
        //"label" => $item->getData('public_name')." (".$this->helper->obfuscate_email($item->getData('email'))." )"
        return json_encode($arrOptions);
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getFormAction()
    {
        $unsavedRfq_model = $this->_coreRegistry->registry('matrix_currentrfqtemplate');
        $id = null;
        if (isset($unsavedRfq_model)) {
            $id = $unsavedRfq_model->getId();
        }
        return $this->getUrl('noncatalogrequesttoquote/index/submit/', ['_secure' => true,'_query'=>['id' => $id]]);
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getUnsavedData()
    {
        return $this->getCurrentRfqTemplate();
        /*$customerId = $this->session->getCustomer()->getId();
        if(!isset($customerId)) return null;

        $obj = $this->objectFactory->create();
        $unsavedRfq_model = $this->_rfqtemplateFactory->create();
        $unsavedRfq_model->load($customerId,'customer_id');
        if($unsavedRfq_model->getId()){
            $result = $unsavedRfq_model->getData('form_data');
            $data = $this->json->unserialize($result);
            if(array_key_exists('uploads',$data)){
              $data['uploadattachement'] =    $this->populateAttachements($data['uploads']);
            }
            $obj->setData('rfqTemplate', $data);
        }else {
             $obj->setData('rfqTemplate', array());
        }
        return $obj;*/
    }

    private function populateAttachements($data)
    {
        $attachmentPath = $this->mediaDirectory->getAbsolutePath(self::NONCAT_RFQ_ATTACHEMEMT_FOLDER);
        $arrImageTypes = ['image/jpeg','image/jpg','image/png','image/gif'];
        if (count($data)<=0) {
            return null;
        }
        $arrUploads =  [];

        foreach ($data as $key => $value) {
            $fileAbsolutePath =  $attachmentPath . '/' . $value;
            $fileRelativePath = $this->getMediaUrl() . self::NONCAT_RFQ_ATTACHEMEMT_FOLDER . '/' . $value;
            if (!file_exists($fileAbsolutePath)) {
                continue;
            }

            //$filePath = $this->mediaDirectory->getFileSize($attachmentPath.$value);
            $fileSize = $this->mediaDirectory->stat(self::NONCAT_RFQ_ATTACHEMEMT_FOLDER . '/' . $value)['size'];
            $type = getimagesize($fileAbsolutePath)['mime'];

            if (!in_array($type, $arrImageTypes)) {
                $fileNamePart = explode('.', $value);
                $type = $fileNamePart[count($fileNamePart)-1];

                switch (strtolower($type)) {
                    case 'pdf':
                        $fileRelativePath = $this->getMediaUrl() . 'wysiwyg/pdf.png';
                        break;

                    case 'doc':
                        $fileRelativePath = $this->getMediaUrl() . 'wysiwyg/doc.png';
                        break;

                    case 'docx':
                        $fileRelativePath = $this->getMediaUrl() . 'wysiwyg/doc.png';
                        break;

                    case 'zip':
                        $fileRelativePath = $this->getMediaUrl() . 'wysiwyg/zip.png';
                        break;

                    default:
                        break;
                }

            }
            $arrFile =  [
            'name'=> $value,
            "type"=> $type,
            "error"=>0,
            'size'=>$fileSize,
            'path'=>$fileAbsolutePath,
            'file'=>$value,
            'url'=>$fileRelativePath,
            'previewType'=> 'image',
            'id' => rand(1, 9999999)
            ];

            $arrUploads[] = $arrFile;
        }
        return $arrUploads;
    }

    public function isApproveCustomer()
    {
        $customerId = $this->session->getCustomer()->getId();
        $customer = $this->_customerFactory->create()->load($customerId);
        $isApproveCustomer =  0;
        if ($customer->getIsApprove()) {
            $isApproveCustomer = 1;
        }
        return $isApproveCustomer;
    }
}
