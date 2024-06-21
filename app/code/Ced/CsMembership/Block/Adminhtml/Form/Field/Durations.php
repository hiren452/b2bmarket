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
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\Form\Field;

use Ced\CsMembership\Model\System\Config\Source\Duration;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Durations (for setting duration field)
 */
class Durations extends Select
{
    /**
     * @var Duration
     */
    protected $duration;

    /**
     * Durations constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param Duration $duration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Duration $duration,
        array $data = []
    ) {
        $this->duration = $duration;
        parent::__construct($context, $data);
    }

    /**
     * Set input name
     *
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render Duration field
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setExtraParams('style="width: 150px;"');
        if (!$this->getOptions()) {
            $this->addOptions($this->duration->toOptionArray());
        }
        return parent::_toHtml();
    }
}
