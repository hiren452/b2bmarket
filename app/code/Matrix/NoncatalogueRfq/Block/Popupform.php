<?php
namespace Matrix\NoncatalogueRfq\Block;

class Popupform extends \Magento\Framework\View\Element\Template
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

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
        $this->formKey = $context->getFormKey();
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

    public function getFormAction()
    {
        return $this->getUrl('noncatalogrequesttoquote/index/popupsubmit');
    }
}
