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

use Magento\Backend\App\Action;

/**
 * Class Categorytree (for category tree in membership form)
 */
class Categorytree extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $csmarketplaceHelper;

    /**
     * @var \Ced\CsMarketplace\Model\VproductsFactory
     */
    protected $vproductsFactory;

    /**
     * Categorytree constructor.
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        Action\Context $context
    ) {
        parent::__construct($context);

        $this->categoryFactory = $categoryFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
        $this->vproductsFactory = $vproductsFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $category_ids = isset($data["category_ids"]) ? $data["category_ids"] : [];

        $category_model = $this->categoryFactory->create()
            ->setStoreId($this->getRequest()->getParam('store', 0));

        $category = $category_model->load($data["categoryId"]);

        $children = $category->getChildren();
        $subCategoryIds = explode(",", $children);

        $result_tree = "";
        $allowed_categories = [];
        $category_mode = 0;
        $category_mode = $this->csmarketplaceHelper->getStoreConfig('ced_vproducts/general/category_mode', 0);
        if ($category_mode) {
            $allowed_categories = explode(
                ',',
                $this->csmarketplaceHelper->getStoreConfig('ced_vproducts/general/category', 0)
            );
        }
        ob_start();

        foreach ($subCategoryIds as $subCategoryId) {
            $_subCategory = $category_model->load($subCategoryId);

            if ($category_mode && !in_array($subCategoryId, $allowed_categories)) {
                continue;
            }
            if ($category_mode) {
                $childrens = count(
                    array_intersect(
                        $category_model->getResource()->getAllChildren($category_model->load($_subCategory->getId())),
                        $allowed_categories
                    )
                ) - 1;
            } else {
                $childrens = count(
                    $category_model->getResource()->getAllChildren($category_model->load($_subCategory->getId()))
                ) - 1;
            }

            if ($childrens > 0) { ?>
                <li class="tree-node">
                    <div class="tree-node-el ced-folder tree-node-collapsed">
                        <span class="tree-node-indent"></span>
                        <img class="tree-ec-icon tree-elbow-line"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <img class="tree-ec-icon tree-elbow-plus"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <img unselectable="on" class="tree-node-icon"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <input class="elements" type="checkbox"
                               name="category[]" <?= in_array($_subCategory->getId(), $category_ids)
                                        ? 'checked' : '' ?>
                               value='<?= $_subCategory->getId() ?>'/>
                        <span class="elements cat_name">
                            <?= $_subCategory->getName() .
                                " (" . $this->vproductsFactory->create()->getProductCount($_subCategory->getId()) . ")"
                ?>
                        </span>
                    </div>
                    <ul class="root-category root-category-wrapper" style="display:none;margin-left: 0px;"></ul>
                </li>
            <?php } else { ?>
                <li class="tree-node">
                    <div class="tree-node-el ced-folder tree-node-leaf">
                        <span class="tree-node-indent"></span>
                        <img class="tree-ec-icon tree-elbow-line"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <img class="tree-ec-icon tree-elbow-end"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <img unselectable="on" class="tree-node-icon"
                             src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">
                        <input class="elements" type="checkbox"
                               name="category[]" <?= in_array($_subCategory->getId(), $category_ids)
                    ? 'checked' : '' ?>
                               value='<?= $_subCategory->getId() ?>'/>
                        <span class="elements cat_name">
                            <?= $_subCategory->getName() .
                    " (" . $this->vproductsFactory->create()->getProductCount($_subCategory->getId()) . ")"
                ?></span>
                    </div>
                </li>
            <?php }
            }

        $result = ob_get_contents();
        ob_end_clean();
        //echo $result;
    }
}
