<?php

namespace Matrix\CustomerMembership\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckRfqLimit implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $responseFactory;

    protected $url;

    protected $request;

    protected $membershipHelper;

    protected $messageManager;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request,
        \Ced\CustomerMembership\Helper\Data $membershipHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->customerSession = $customerSession;
        $this->responseFactory = $responseFactory;
        $this->url             = $url;
        $this->request      = $request;
        $this->membershipHelper = $membershipHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->customerSession->isLoggedIn()) {

            $count = $this->membershipHelper->getRemainingRfq();

            $existing_subscription = $this->membershipHelper->getExistingSubcription($this->customerSession->getId());

            if (isset($existing_subscription[0]['rfq_limit'])) {
                $rfq_limit = $existing_subscription[0]['rfq_limit'];

                if ($count >= $rfq_limit) {
                    $this->messageManager->addErrorMessage(__('RFQ Creation limit has been Exceeded.'));
                    $url = $this->url->getUrl('membership/membership/view');
                    $this->responseFactory->create()->setRedirect($url)->sendResponse();
                }
            }

        }
    }
}
