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

namespace Ced\CsAuction\Controller\AddAuction;

use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlFactory;

/**
 * Class Save
 *
 * @package Ced\CsAuction\Controller\AddAuction
 */
class Save extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Ced\Auction\Model\Auction
     */
    protected $auction;

    /**
     * @var DateTime
     */
    protected $datetime;

    /**
     * @var TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var
     */
    protected $biddetails;

    /**
     * Save constructor.
     *
     * @param \Ced\Auction\Model\Auction                       $auction
     * @param DateTime                                         $datetime
     * @param TimezoneInterface                                $timezoneInterface
     * @param CollectionFactory                                $biddetails
     * @param Context                                          $context
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory
     * @param Session                                          $customerSession
     * @param UrlFactory                                       $urlFactory
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Ced\CsMarketplace\Helper\Data                   $csmarketplaceHelper
     * @param \Ced\CsMarketplace\Helper\Acl                    $aclHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory           $vendor
     */
    public function __construct(
        \Ced\Auction\Model\Auction $auction,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $biddetails,
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Session $customerSession,
        UrlFactory $urlFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper,
        \Ced\CsMarketplace\Helper\Acl $aclHelper,
        \Ced\CsMarketplace\Model\VendorFactory $vendor
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $customerSession,
            $urlFactory,
            $registry,
            $jsonFactory,
            $csmarketplaceHelper,
            $aclHelper,
            $vendor
        );

        $this->customerSession = $customerSession;
        $this->auction = $auction;
        $this->datetime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->biddetailsCollection = $biddetails;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {

        $postdata = $this->getRequest()->getPostValue();
        //auction save

        if($postdata && $this->getRequest()->getParam('id')) {
            $resultRedirect = $this->resultRedirectFactory->create();

            $date = $this->datetime->gmtDate();
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');

            $extended = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->modify('-1 minutes')
                ->format('Y-m-d H:i:s');

            $endtime = $this->timezoneInterface
                ->date(new \DateTime($postdata['end_datetime']))
                ->format('Y-m-d H:i:s');

            if(strtotime($postdata['start_datetime'])< strtotime($extended)) {
                $this->messageManager
                    ->addErrorMessage('Bidding start time must be greater than current time');
                return $resultRedirect->setPath(
                    'csauction/addauction/auctionform',
                    ['product_id' => $this->getRequest()->getParam('product_id')]
                );
            }
            if(strtotime($endtime) < strtotime($postdata['start_datetime'])) {
                $this->messageManager
                    ->addErrorMessage('Bidding end time must be greater than bidding start time');
                return $resultRedirect->setPath(
                    'csauction/addauction/auctionform',
                    ['product_id' => $this->getRequest()->getParam('product_id')]
                );
            }

            if(strtotime($postdata['start_datetime']) > strtotime($currenttime)) {
                $postdata['status'] = 'not started';
            }
            if(strtotime($postdata['start_datetime']) <= strtotime($currenttime)) {
                $postdata['status'] = 'processing';
            }

            if($postdata['Sell_Product'] == 'yes') {
                if($postdata['Starting_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['product_id' => $this->getRequest()->getParam('product_id')]);
                }
                if($postdata['Max_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Maximum bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['product_id' => $this->getRequest()->getParam('product_id')]);
                }
                if($postdata['Max_Price'] == null) {
                    $postdata['Max_Price'] = $postdata['Price'];
                }
            }

            $this->auction->setProductName($postdata['Product_Name']);
            $this->auction->setStartingPrice($postdata['Starting_Price']);
            $this->auction->setMaxPrice($postdata['Max_Price']);
            $this->auction->setStartDatetime($postdata['start_datetime']);
            $this->auction->setEndDatetime($postdata['end_datetime']);
            $this->auction->setProductId($this->getRequest()->getParam('product_id'));
            $this->auction->setVendorId($this->customerSession->getVendorId());
            $this->auction->setStatus($postdata['status']);
            $this->auction->setSellproduct($postdata['Sell_Product']);
            $this->auction->setTempStartdate($postdata['start_datetime']);
            $this->auction->setTempEnddate($postdata['end_datetime']);
            $this->auction->save();

            $this->messageManager->addSuccessMessage(__('Auction has been save successfully'));
            return $this->_redirect('csauction/auctionlist/index');
        }

        // auction edit

        if($postdata && $this->getRequest()->getParam('id')) {
            $resultRedirect = $this->resultRedirectFactory->create();

            $date = $this->datetime->gmtDate();
            $currenttime = $this->timezoneInterface
                ->date(new \DateTime($date))
                ->format('Y-m-d H:i:s');

            $bid =$this->biddetailsCollection->create()
                ->addFieldToFilter('product_id', $this->getRequest()->getParam('product_id'))
                ->addFieldToFilter('status', 'bidding');

            if(count($bid)> 0) {

                if (strtotime($postdata['Start_Datetime']) != strtotime($postdata['Temp_Startdate'])) {
                    $this->messageManager->addErrorMessage('Bid has been made so start datetime could not be change');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
            }

            if($postdata['Max_Price'] != null && $postdata['Max_Price'] != 0) {
                if($postdata['Starting_Price'] >= $postdata['Max_Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than maximum bid');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
            }

            if($postdata['Sell_Product'] == 'yes') {

                if ($postdata['Starting_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
                if ($postdata['Max_Price'] > $postdata['Price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than original product price');
                    return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
                }
                if ($postdata['Max_Price'] == null) {
                    $postdata['Max_Price'] = $postdata['Price'];
                }
            }

            if(strtotime($postdata['End_Datetime']) < strtotime($postdata['Start_Datetime'])) {
                $this->messageManager->addErrorMessage('Bidding end time must be greater than bidding start time');
                return $resultRedirect->setPath('csauction/addauction/auctionform', ['id' => $this->getRequest()->getParam('id')]);
            }
            if(strtotime($postdata['Start_Datetime']) > strtotime($currenttime)) {
                $data['status'] = 'not started';
            }

            if(strtotime($postdata['Start_Datetime']) <= strtotime($currenttime)) {
                $postdata['status'] = 'processing';
            }

            $this->auction->load($this->getRequest()->getParam('id'));
            $this->auction->setMaxPrice($postdata['Max_Price']);
            $this->auction->setStartDatetime($postdata['Start_Datetime']);
            $this->auction->setEndDatetime($postdata['End_Datetime']);
            $this->auction->setSellproduct($postdata['Sell_Product']);
            $this->auction->setTempStartdate($postdata['Start_Datetime']);
            $this->auction->setTempEnddate($postdata['End_Datetime']);
            $this->auction->save();

            $this->messageManager->addSuccessMessage(__('Auction has been save successfully'));
            return $this->_redirect('csauction/auctionlist/index');
        } else {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving auction'));
            return $this->_redirect('csauction/auctionlist/index');
        }
    }
}
