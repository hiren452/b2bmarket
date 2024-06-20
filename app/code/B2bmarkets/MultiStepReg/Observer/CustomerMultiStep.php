<?php

namespace B2bmarkets\MultiStepReg\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerMultiStep implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $vendor;

    protected $customerRepositoryInterface;

    protected $responseFactory;

    protected $url;

    protected $request;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMarketplace\Model\Vendor $vendor,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->vendor          = $vendor;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->responseFactory = $responseFactory;
        $this->url             = $url;
        $this->request      = $request;
    }

    /**
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //if logged in
        if ($this->customerSession->isLoggedIn()) {
            //seller or not
            $vendor = $this->vendor->loadByCustomerId($this->customerSession->getCustomerId());
            if ($vendor && $vendor->getId()) {
                //nothing to do
            } else {
                //check multi step done or not
                $customer = $this->customerRepositoryInterface->getById($this->customerSession->getCustomerId());
                //$customer = $this->customerSession->getCustomer();
                if (empty($customer->getCustomAttribute('buyer_multi_step'))) {
                    $route      = strtolower($this->request->getRouteName());
                    $controller = strtolower($this->request->getControllerName());
                    $action     = strtolower($this->request->getActionName());

                    $path = $route . '/' . $controller . '/' . $action;
                    $allowed = ['multistepreg/index/index', 'multistepreg/index/save', 'customer/account/logout'];
                    if (!in_array($path, $allowed)) {
                        $url = $this->url->getUrl('multistepreg/index/index');
                        $this->responseFactory->create()->setRedirect($url)->sendResponse();
                    }
                }
            }
        }
    }
}
