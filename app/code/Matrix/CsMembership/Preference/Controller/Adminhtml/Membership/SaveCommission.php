<?php

namespace Matrix\CsMembership\Preference\Controller\Adminhtml\Membership;

use Ced\CsMembership\Controller\Adminhtml\Membership\Save;
use Ced\CsMembership\Model\ImageUploader as imageuploders;
use Ced\CsMembership\Model\MembershipFactory;
use Ced\CsMembership\Model\ResourceModel\Membership as MembershipResource;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class SaveCommission extends Save
{
    public $imageUploader;
    private $membershipResource;

    public function __construct(
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        MembershipFactory $membershipFactory,
        MembershipResource $membershipResource,
        Context $context,
        imageuploders $imageUploader,
        ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {
        parent::__construct(
            $filesystem,
            $uploaderFactory,
            $storeManager,
            $membershipFactory,
            $imageUploader,
            $productRepositoryFactory,
            $context
        );
        $this->membershipResource = $membershipResource;
    }

    public function execute()
    {
        $post_data = $this->getRequest()->getPostValue();
        if ($this->getRequest()->isPost()) {
            $redirectBack = $this->getRequest()->getParam('back', false);

            if (isset($post_data['image']['delete']) && $post_data['image']['delete'] == 1) {
                if ($post_data['image']['value'] != '') {
                    $post_data['image'] = '';
                }
            }
            if (!empty($post_data['image']['value'])) {
                $post_data['image'] = $post_data['image']['value'];
            }

            /*
            *upload image
            */
            if (isset($post_data['image'][0]['name']) && isset($post_data['image'][0]['tmp_name'])) {
                $post_data['image'] = $post_data['image'][0]['name'];
                $this->imageUploader->moveFileFromTmp($post_data['image']);
            } elseif (isset($post_data['image'][0]['name']) && !isset($post_data['image'][0]['tmp_name'])) {
                $post_data['image'] = $post_data['image'][0]['name'];
            } else {
                $post_data['image'] = '';
            }

            $mode = '';
            $editid = $this->getRequest()->getParam('id');
            if ($editid) {
                $mode = \Ced\CsMembership\Model\Membership::PRODUCT_EDIT;
            } else {
                $mode = \Ced\CsMembership\Model\Membership::PRODUCT_NEW;
            }

            $product_id = new \Magento\Framework\DataObject();
            /*
            *to create associates virtual product
            */
            try {

                $this->_eventManager->dispatch('create_membership_virtual_product', ['result' => $product_id, 'mode' => $mode, 'editid' => $editid, 'postdata' => $post_data]);

                // if ($product_id->getResult()) {
                $product = $this->productRepositoryFactory->create()
                    ->getById($product_id->getResult());
                if ($product->getData('image')) {
                    $post_data['image'] = $product->getData('image');
                }
                $post_data['product_id'] = $product_id->getResult();
                $post_data['store'] = $this->storeManager->getStore(null)->getStoreId();
                // if (!$this->getRequest()->getParam('id'))
                if (isset($post_data['category_ids'])) {
                    $post_data['category_ids'] = implode(',', $post_data['category_ids']);
                } else {
                    $post_data['category_ids'] = '';
                }

                $model = $this->membershipFactory->create()
                    ->addData($post_data)
                    ->setId($this->getRequest()->getParam("id"));
                $model->save();
                $this->messageManager->addSuccessMessage(__('Membership Plan Saved Successfully.'));
                // }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect("*/*/");
                return;
            }

            if ($redirectBack && $model) {
                $this->_redirect('*/*/edit', [
                    'id' => $model->getId(),
                    '_current' => true
                ]);
                return;
            }
            $this->_redirect("*/*/");
        } else {
            $this->_redirect("*/*/");
        }
    }
}
