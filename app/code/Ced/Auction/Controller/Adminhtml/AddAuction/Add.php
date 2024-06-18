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
 * @package   Ced_Auction
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (https://cedcommerce.com/)
 * @license   https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Auction\Controller\Adminhtml\AddAuction;

use Ced\Auction\Model\ResourceModel\Auction\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Add
 *
 * @package Ced\Auction\Controller\Adminhtml\AddAuction\Add
 */
class Add extends \Magento\Backend\App\Action
{
    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        CollectionFactory $collectionFactory,
        TimezoneInterface $timezoneInterface,
        DateTime $datetime
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->collectionFactory = $collectionFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->datetime = $datetime;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        $post = $this->getRequest()->getParams();
        $this->coreRegistry->register('auction_form_data', $post);
        $this->coreRegistry->register('product_id', $post['id']);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_Auction::New Auction Form');

        $resultPage->getConfig()->getTitle()->prepend(__('Create  New Auction'));
        return $resultPage;
    }

    public function _isAllowed()
    {
        return true;
    }
}
