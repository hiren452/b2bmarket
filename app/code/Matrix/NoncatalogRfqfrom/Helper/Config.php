<?php
namespace Matrix\NoncatalogRfqfrom\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Config helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_NONCATALOGFRQFROM_ATTRIBUTE_DATA = 'noncatalogrfq_configuration/attributes/data';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->context = $context;
    }

    /**
     * Get attribute details
     *
     * @param null $store
     * @return bool|array
     */
    public function getAttributeData($store = null)
    {
        $data = $this->scopeConfig->getValue(
            self::XML_PATH_NONCATALOGFRQFROM_ATTRIBUTE_DATA,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        if(empty($data)) {
            return false;
        }
        try {
            $data = str_replace("\n", '', $data);
            $data = str_replace("\t", '', $data);
            $data = json_decode($data, true);
            if(is_array($data)) {
                return $data;
            }
            return false;
        } catch (\Exception $e) {

        }
    }
}
