<?php
namespace  Matrix\NoncatalogueRfq\Model\Config\Source\Email;

class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_coreRegistry = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
    }

    public function toOptionArray()
    {
        /** @var $collection \Magento\Email\Model\ResourceModel\Template\Collection */
        /*if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
          $collection = $this->_templatesFactory->create();
          $collection->load();
          $this->_coreRegistry->register('config_system_email_template', $collection);
        }*/
        $collection = $this->_templatesFactory->create();
        $collection->load();
        $options = $collection->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => 'Select Template']);
        //$templateId = str_replace('/', '_', $this->getPath());
        //$templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        //$templateLabel = __('%1 (Default)', $templateLabel);
        //array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        return $options;
    }
}
