<?php

namespace Matrix\NoncatalogueRfq\Block\Category;

use Magento\Framework\View\Element\Template\Context;

class Select extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    public $productFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Session
     */
    public $session;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Select constructor.
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Ced\CsMarketplace\Model\Session $session
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ced\CsMarketplace\Model\Session $session,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_type = $type;
        $this->_categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->session = $session;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategories()
    {
        $level = 2;
        $this->options = [];
        $collection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect(['name', 'is_active', 'parent_id', 'label', 'level', 'children'])
            //->addAttributeToFilter('level' , $level);
            ->addAttributeToFilter('entity_id', ['neq' => \Magento\Catalog\Model\Category::TREE_ROOT_ID]);
        //return $collection;
        $categoryById = [
            \Magento\Catalog\Model\Category::TREE_ROOT_ID => [
                'id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['level'] = $category->getLevel();
            $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
        }

        $this->renederCat($categoryById[\Magento\Catalog\Model\Category::TREE_ROOT_ID]['children']);

        return $this->options;
    }

    /**
     * @param $data
     */
    public function renederCat($data)
    {

        foreach ($data as $cat) {
            $this->options[] = ['value' => $cat['id'], 'label' => __($cat['label'])];
        }
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->_type->toOptionArray(false, true);
    }

    /**
     * @param $id
     * @return array
     */
    public function getProductDetails($id)
    {
        $product = $this->productFactory->create()->load($id);

        return ['type' => $product->getTypeId(), 'cats' => $product->getCategoryIds()];
    }
}
