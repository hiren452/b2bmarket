<?php

namespace Matrix\CsMultistepreg\Controller\Multistep;

use Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory as VendorCollection;
use Ced\CsMarketplace\Model\Session;
use Magento\Backend\App\Action\Context;

class CheckShopUrl extends \Magento\Framework\App\Action\Action
{

    public $session;

    public $vendorCollection;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    protected $jsonResultFactory;

    protected $vendor;

    protected $urlRewriteFactory;

    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        VendorCollection $vendorCollection,
        Session $session
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->vendorCollection = $vendorCollection;
        $this->vendorFactory = $vendorFactory;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->vendor = $vendor;
        $this->urlRewriteFactory = $urlRewriteFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $params = $this->getRequest()->getParams();

        $vendorId = $this->session->getCustomerId();//vendor id is storing in this session

        $shopUrlKey = $this->vendor->formatShopUrl($params['shop_url']);

        $vData  = $this->vendorCollection->create()
            ->addAttributeToFilter('shop_url', $shopUrlKey)
            ->addAttributeToFilter('entity_id', ['nin' => $vendorId]);

        if (count($vData->getData()) > 0) {
            $data['success']  = 0;
            $data['shop_url'] = $shopUrlKey;
            $data['msg']      = __('Shop url already exist. Please Provide another Shop Url');
        } else {
            $data['success']  = 1;
            $data['shop_url'] = $shopUrlKey;
            $data['msg']      = __('You can use this url');
        }

        $result = $this->jsonResultFactory->create();

        return $result->setData($data);
    }
}
