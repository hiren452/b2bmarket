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

namespace Ced\CsAuction\Block\AddAuction;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Timer
 *
 * @package Ced\CsAuction\Block\AddAuction
 */
class Timer extends Template
{
    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * Timer constructor.
     *
     * @param TimezoneInterface $timezoneInterface
     * @param Template\Context  $context
     * @param array             $data
     */
    public function __construct(
        TimezoneInterface $timezoneInterface,
        Template\Context $context,
        array $data = []
    ) {
        $this->timezoneInterface = $timezoneInterface;
        parent::__construct($context, $data);

    }

    /**
     * @return TimezoneInterface
     */
    public function getTimeZone()
    {
        return $this->timezoneInterface->date()
            ->format('Y-m-d H:i:s');
    }
}
