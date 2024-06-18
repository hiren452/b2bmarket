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
 * @category  Ced
 * @package   Ced_CsAuction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsAuction\Model;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Class AuctionForm
 *
 * @package Ced\CsAuction\Model
 */
class AuctionForm extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'address';
    /**
     * @var array
     */
    protected $_fields = [];
    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var \Ced\CsMarketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * AuctionForm constructor.
     *
     * @param \Ced\CsMarketplace\Helper\Data                               $marketplaceHelper
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory            $extensionFactory
     * @param AttributeValueFactory                                        $customAttributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        \Ced\CsMarketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId) {
            return $this->marketplaceHelper->getStore($storeId);
        } else {
            return $this->marketplaceHelper->getStore();
        }
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStoreId()
    {
        return $this->getStore()->getId();
    }

    /**
     * Get the code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get the code separator
     *
     * @return string
     */
    public function getCodeSeparator()
    {
        return $this->_codeSeparator;
    }

    /**
     *  Retreive input fields
     *
     * @return array
     */
    public function getFields()
    {
        $this->_fields = [];
        $this->_fields['product_name'] = ['type' => 'text', 'required' => false];
        $this->_fields['price'] = ['type' => 'text', 'required' => false];
        $this->_fields['starting_price'] = ['type' => 'text', 'required' => true];
        $this->_fields['max_price'] = ['type' => 'text', 'required' => true];
        $this->_fields['start_datetime'] = ['type' => 'text', 'required' => true];
        $this->_fields['end_datetime'] = ['type' => 'text', 'required' => true];
        $this->_fields['extended_time'] = ['type' => 'text', 'required' => false];
        $this->_fields['status'] = ['type' => 'select', 'required' => false,
            'values' => [['label' => 'Processing', 'value' => 'processing'],
                ['label' => 'Complete', 'value' => 'complete'], ['label' => 'BidClosed', 'value' => 'bidclosed']]];
        return $this->_fields;
    }

    /**
     * Retreive labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label':
                return __('Auction Details');
                break;
            case 'product_name':
                return __('Product Name');
                break;
            case 'price':
                return __('Original Product Price');
                break;
            case 'starting_price':
                return __('Starting Price');
                break;
            case 'max_price':
                return __('Max Price');
                break;
            case 'start_datetime':
                return __('Start Date-Time');
                break;
            case 'end_datetime':
                return __('End Date-Time');
                break;
            case 'extended_time':
                return __('Extended Time');
                break;
            case 'status':
                return __('Status');
                break;
            default:
                return __($key);
                break;
        }
    }
}
