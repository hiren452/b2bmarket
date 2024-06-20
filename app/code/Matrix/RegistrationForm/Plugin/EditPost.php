<?php

namespace Matrix\RegistrationForm\Plugin;

use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class EditPost
{

    protected $messageManager;

    protected $customerRepository;

    protected $addressRepository;

    protected $request;

    protected $session;

    private $resultRedirectFactory;

    /**
     * EditPost constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        Session $customerSession,
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->request = $request;
        $this->session = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * This functions sets the company and telephone customer attributes from manage profile.
     *
     * @param \Magento\Customer\Controller\Account\EditPost $subject
     * @param Redirect $result
     * @return Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterExecute(\Magento\Customer\Controller\Account\EditPost $subject, Redirect $result): Redirect
    {
        $postData = $this->request->getPost();
        if (isset($postData['company'])) {
            $customer = $this->customerRepository->getById($this->session->getCustomer()->getId());
            $shippingAddressId = $customer->getDefaultShipping();
            if ($shippingAddressId == null) {
                $this->messageManager->addErrorMessage("Please fill and save a customer address
                to save company and telephone");
                return $this->resultRedirectFactory->create()->setPath('customer/address/new');
            }
            try {
                $shippingAddress = $this->addressRepository->getById($shippingAddressId);
                $shippingAddress->setCompany($postData['company']);
                if (isset($postData['telephone'])) {
                    $shippingAddress->setTelephone($postData['telephone']);
                }
                $this->addressRepository->save($shippingAddress);
            } catch (Exception $e) {
                throw $e;
            }
            if (!empty($subject->getRequest()->getParam('next_step'))) {
                $path = 'customer/account';
            } else {
                $path = 'customer/account/edit';
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($path);
        }

        return $result;
    }
}
