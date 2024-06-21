<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Controller\Adminhtml\Membership;

/**
 * Class Save (Saving membership)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    protected $productRepositoryFactory;

    /**
     * Save constructor.
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Ced\CsMembership\Model\ImageUploader $imageUploader,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->membershipFactory = $membershipFactory;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->imageUploader = $imageUploader;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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

                if ($product_id->getResult()) {
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
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect("*/*/");
                return;
            }

            if ($redirectBack) {
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
