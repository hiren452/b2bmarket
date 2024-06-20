<?php
namespace Matrix\NoncatalogueRfq\Block;

use Magento\Catalog\Model\CategoryFactory;

class Categories extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    protected $adminCategoryTree;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Catalog\Block\Adminhtml\Category\Tree $adminCategoryTree,
        Magento\Catalog\Ui\Component\Product\Form\Categories\Options $optionTree,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->formKey = $context->getFormKey();
        $this->adminCategoryTree = $adminCategoryTree;
        $this->categoryFactory = $categoryFactory;
        $this->optionTree = $optionTree;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return this
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getTree()
    {
        return $this->adminCategoryTree->getTree();
    }

    public function getCategoriesTree()
    {
        $categories = $this->optionTree->toOptionArray();
        return json_encode($categories[0]['optgroup']);
    }

    public function getCategoryById($catdId)
    {
        return $this->categoryFactory->create()->load($catdId);
    }
}
