<?php

namespace Matrix\RfqEntity\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

class Bookmark extends \Magento\Ui\Component\Bookmark
{

    /**
     * @var \Matrix\RfqEntity\Model\RfqEntity
     */
    protected $rfqentity;

    /**
     * @param  ContextInterface                $context
     * @param  \Matrix\RfqEntity\Model\RfqEntity $rfqentity
     * @param  BookmarkRepositoryInterface     $bookmarkRepository
     * @param  BookmarkManagementInterface     $bookmarkManagement
     * @param  {Array}  array                  $components
     * @param  {Array}  array                  $data
     */
    public function __construct(
        ContextInterface $context,
        \Matrix\RfqEntity\Model\RfqEntity $rfqentity,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
        $this->rfqentity = $rfqentity;
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        $namespace = $this->getContext()->getRequestParam('namespace', $this->getContext()->getNamespace());
        $config = [];
        if (!empty($namespace)) {
            $storeId = $this->getContext()->getRequestParam('store');

            if (empty($storeId)) {
                $storeId = $this->getContext()->getFilterParam('store_id');
            }

            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());

                if (!empty($storeId)) {
                    $config['current']['filters']['applied']['store_id'] = $storeId;
                }
            }
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration($this)));

        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
