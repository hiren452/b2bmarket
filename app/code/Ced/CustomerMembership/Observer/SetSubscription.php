<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_CustomerMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Observer;

use Ced\CustomerMembership\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SetSubscription implements ObserverInterface
{
    protected $request;
    private $messageManager;

    public function __construct(
        RequestInterface $request,
        ManagerInterface $messageManager,
        Data $helperData
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->helperData = $helperData;
    }
    /**
     * Product Assignment Tab
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderids = $observer->getEvent()->getOrderIds();

        if (count($orderids)>0) {
            foreach ($orderids as $_orderid) {
                $this->helperData->setSubscription($_orderid);
            }
        }
    }
}
