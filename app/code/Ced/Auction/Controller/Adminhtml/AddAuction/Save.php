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

use Ced\Auction\Model\AuctionFactory;
use Ced\Auction\Model\ResourceModel;
use Ced\Auction\Model\ResourceModel\BidDetails\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;

class Save extends Action
{

    public $auctionFactory;

    public $timezoneInterface;

    public $datetime;

    public $biddetails;

    public $auctionResource;

    public $biddetailsCollection;

    public $backendSession;

    public $url;

    /**
     *
     * @param Action\Context                 $context
     * @param AuctionFactory                 $auctionFactory
     * @param ResourceModel\Auction          $auctionResourceModel
     * @param DateTime                       $datetime
     * @param TimezoneInterface              $timezoneInterface
     * @param UrlInterface                   $url
     * @param CollectionFactory              $biddetails
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        Action\Context $context,
        AuctionFactory $auctionFactory,
        ResourceModel\Auction $auctionResourceModel,
        DateTime $datetime,
        TimezoneInterface $timezoneInterface,
        UrlInterface $url,
        CollectionFactory $biddetails,
        \Magento\Backend\Model\Session $backendSession
    ) {
        parent::__construct($context);
        $this->auctionFactory = $auctionFactory;
        $this->auctionResource = $auctionResourceModel;
        $this->datetime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->url = $url;
        $this->biddetailsCollection = $biddetails;
        $this->backendSession = $backendSession;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        /*return $this->_authorization->isAllowed('Ced_Auction::save');*/
        return true;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $path = 'auction/addAuction/index';
        $params = ['_current' => true];

        /**
 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
*/
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $auctionModel = $this->auctionFactory->create();
            $id = $this->getRequest()->getParam('id');
            $pId = $this->getRequest()->getParam('p_id');
            $path = 'auction/addAuction/edit';
            $params['id'] = $id;
            $proceed = true;

            $date = $this->datetime->gmtDate();
            $currenttime = $this->getTimeFormatted($date);

            if (!empty($id)) {
                if($data['starting_price'] == 0) {
                    $this->messageManager->addErrorMessage('Starting bid must be greater than 0');
                    return $resultRedirect->setPath($path, ['id' => $id]);
                } elseif($data['max_price'] != null && $data['starting_price'] >= $data['max_price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than Max Bid');
                    return $resultRedirect->setPath($path, ['id' => $id]);
                }
                // Bid not started
                $bid = $this->biddetailsCollection->create()
                    ->addFieldToFilter('product_id', $data['product_id'])
                    ->addFieldToFilter('status', 'bidding');

                if (count($bid) > 0) {
                    $temp = $this->getTimeFormatted($data['temp_startdate']);
                    $loadtemp = $this->getTimeFormatted($data['start_datetime']);

                    if ($temp != $loadtemp) {
                        $this->messageManager->addErrorMessage(
                            'Bid has been made so start datetime could not be change'
                        );
                        return $resultRedirect->setPath(
                            $path,
                            $params
                        );
                    }
                }

                $data['temp_startdate'] = $data['start_datetime'];
                $data['temp_enddate'] = $data['end_datetime'];

                $data['start_datetime'] = $this->getTimeFormatted($data['start_datetime']);
                $data['end_datetime'] = $this->getTimeFormatted($data['end_datetime']);

                if (!$this->validateData($data)) {
                    return $resultRedirect->setPath($path, $params);
                }

                $this->getAuctionResource()->load($auctionModel, $id);
            } else {
                if($data['starting_price'] == 0) {
                    $this->messageManager->addErrorMessage('Starting bid must be greater than 0');
                    return $resultRedirect->setPath('auction/addAuction/add', ['id' => $pId]);
                } elseif($data['max_price'] != null && $data['starting_price'] >= $data['max_price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than Max Bid');
                    return $resultRedirect->setPath('auction/addAuction/add', ['id' => $pId]);
                }
                if (!$this->checkExist($pId)) {
                    $path = 'auction/addAuction/add';
                    $params['id'] = $pId;

                    $data['temp_startdate'] = $data['start_datetime'];
                    $data['temp_enddate'] = $data['end_datetime'];

                    $data['start_datetime'] = $this->getTimeFormatted($data['start_datetime']);
                    $data['end_datetime'] = $this->getTimeFormatted($data['end_datetime']);

                    $extended = $this->timezoneInterface
                        ->date(new \DateTime($date))
                        ->modify('-1 minutes')
                        ->format('Y-m-d H:i:s');

                    if ($data['start_datetime'] < $extended) {
                        $this->messageManager->addErrorMessage(
                            'Bidding start time must be greater than current time'
                        );
                        return $resultRedirect->setPath($path, $params);
                    }

                    if (!$this->validateData($data)) {
                        return $resultRedirect->setPath($path, $params);
                    }

                    if ($data['max_price'] != null && $data['max_price'] != 0) {
                        if ($data['starting_price'] >= $data['max_price']) {
                            $this->messageManager->addErrorMessage('Starting bid must be less than maximum bid');
                            return $resultRedirect->setPath($path, $params);
                        }
                    }

                    $data['product_id'] = $pId;
                } else {
                    $proceed = false;
                }
            }

            if ($proceed) {
                try {
                    $data['status'] = ($data['start_datetime'] <= $currenttime) ? 'processing' : 'not started';
                    $auctionModel->setData($data);
                    /*$this->_eventManager->dispatch(
                        'ced_auction_prepare_savebefore',
                        ['auction' => $auctionModel, 'request' => $this->getRequest()]
                    );*/
                    $this->getAuctionResource()->save($auctionModel);

                    $this->messageManager->addSuccessMessage(__('You saved this Auction.'));
                    $this->backendSession->setFormData(false);
                    if ($this->getRequest()->getParam('back')) {
                        return $resultRedirect->setPath(
                            $path,
                            ['id' => $auctionModel->getId(), '_current' => true]
                        );
                    }

                    return $resultRedirect->setPath('*/*/');
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the post.'));
                }

                $this->_getSession()->setFormData($data);
                return $resultRedirect->setPath($path, $params);
            }
        }

        return $resultRedirect->setPath($path, $params);
    }

    /**
     * @return ResourceModel\Auction
     */
    protected function getAuctionResource()
    {
        return $this->auctionResource;
    }

    protected function getTimeFormatted($time, $format = 'Y-m-d H:i:s')
    {
        try {
            return $this->timezoneInterface
                ->date(new \DateTime($time))
                ->format($format);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateData($data)
    {
        if($data['sellproduct'] == 'yes') {
            if ($data['starting_price'] > $data['product_price']) {
                $this->messageManager->addErrorMessage(
                    'Starting bid must be less than original product price'
                );
                return false;
            }

            if ($data['max_price'] > $data['product_price']) {
                $this->messageManager->addErrorMessage(
                    'Maximum Bidding price must be less than original product price'
                );
                return false;
            }

            if ($data['max_price'] == null) {
                $data['max_price'] = $data['product_price'];
            }

            if ($data['max_price'] != null && $data['max_price'] != 0) {
                if ($data['starting_price'] > $data['max_price']) {
                    $this->messageManager->addErrorMessage('Starting bid must be less than maximum bid');
                    return false;
                }
            }
        }

        if ($data['end_datetime'] < $data['start_datetime']) {
            $this->messageManager->addErrorMessage(
                'Bidding end time must be greater than bidding start time'
            );
            return false;
        }

        return true;
    }

    protected function checkExist($productId)
    {
        /* @var $auctionCollection ResourceModel\Auction\Collection */
        $auctionCollection = $this->auctionFactory->create()->getCollection();
        $auctionCollection->addFieldToFilter('product_id', $productId)->addFieldToFilter('status', 'processing');
        $count = $auctionCollection->getSize();
        return (!empty($count)) ?: false;
    }

}
