<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  Ced
 * @package   Ced_Preorder
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CustomerMembership\Cron;

use Ced\CustomerMembership\Helper\Data;
use Ced\CustomerMembership\Model\ResourceModel\Subscription\Collection as subCollectionFactory;
use Ced\CustomerMembership\Model\SubscriptionFactory;

class Cron
{
    public $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        subCollectionFactory $subCollectionFactory,
        SubscriptionFactory $subscriptionFactory,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->subCollectionFactory = $subCollectionFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->helperData = $helperData;
    }

    /**
     * @return \Ced\Preorder\Helper\Order
     */
    public function execute()
    {

        try {
            $cur_time   =  date('Y-m-d');
            $collection = $this->subCollectionFactory->getCollection()->addFieldToFilter('status', 'running');
            foreach ($collection as $subcription) {
                $model= $this->subscriptionFactory->create();
                $end_date = date_create($subcription->getData('end_date'));
                $curr_date= date_create($cur_time);
                $interval = date_diff($curr_date, $end_date, false);
                if ($interval->format('%a')<=0) {
                    $model->load($subcription->getId());
                    $model->setStatus('expired');
                    $model->save();
                    try {
                        $this->helperData->sendExpirationEmail($subcription);
                    } catch (Exception $e) {
                        $this->logger->debug($e->getMessage());
                    }
                } else {
                    continue;
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        //return  $order;
    }
}
