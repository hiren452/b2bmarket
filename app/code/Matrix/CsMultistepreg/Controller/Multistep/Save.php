<?php

namespace Matrix\CsMultistepreg\Controller\Multistep;

use Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory as VendorCollection;
use Ced\CsMarketplace\Model\VendorFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface;

class Save extends \Ced\CsMultistepreg\Controller\Multistep\Save
{
    public $vendorCollection;

    public function __construct(
        Context $context,
        VendorFactory $vendorFactory,
        ManagerInterface $manager,
        VendorCollection $vendorCollection
    ) {
        $this->vendorCollection = $vendorCollection;
        parent::__construct($context, $vendorFactory, $manager);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $vendorId = $this->getRequest()->getParam('vendor_id');
        if ($vendorId) {

            //=== check shop url ======
            $params = $this->getRequest()->getParam('vendor');

            $vendor = $this->vendorFactory->create()->load($vendorId);

            if ($vendor->getOnboardingCompleted() != 1) {

                $vData = $this->vendorCollection->create()
                    ->addFieldToFilter('shop_url', $params['shop_url'])
                    ->addAttributeToFilter('entity_id', ['nin' => $vendorId]);

                if (count($vData->getData()) > 0) {
                    $this->messageManager
                        ->addErrorMessage(__('Shop url already exist1. Please Provide another Shop Url'));
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }
            }

            //=== check shop url ======

            $vendor->addData($this->getRequest()->getParam('vendor'));
            try {
                $vendor->save();
                $this->manager
                    ->dispatch('vendor_multistepregistration_complete_after', ['vendor' => $vendor]);
                return $this->_redirect('csmarketplace/account/approval');
            } catch (Exception $e) {
                throw new Exception(__($e->getMessage()));
            }
        }
    }
}
