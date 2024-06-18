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
 * @package     Ced_RequestToQuote
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\RequestToQuote\Controller\Customer;

class Poapprove extends \Magento\Framework\App\Action\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\RequestToQuote\Model\PoFactory $pofactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\RequestToQuote\Model\PoFactory $pofactory,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->poFactory = $pofactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $poid = $this->getRequest()->getParam('po_id');
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $po = $this->poFactory->create()->load($poid);
        if ($po && $po->getId()) {
            if ($po->getPoCustomerId() == $this->session->getCustomerId()) {
                if($po->getStatus() == \Ced\RequestToQuote\Model\Po::PO_STATUS_PENDING) {
                    $po->setData('status', \Ced\RequestToQuote\Model\Po::PO_STATUS_CONFIRMED);
                    $po->save();
                }
                $this->messageManager->addSuccessMessage(__('%1 has been approved successfully.', '#' . $po->getPoIncrementId()));
                $resultRedirect->setPath('requesttoquote/customer/editpo', ['poId' => $poid]);
                return $resultRedirect;
            }
            $this->messageManager->addErrorMessage(__('This Po does not belongs to you.'));
            $resultRedirect->setPath('requesttoquote/customer/po');
            return $resultRedirect;
        } else {
            $this->messageManager->addErrorMessage(__('This Po no longer exist.'));
        }
        $resultRedirect->setPath('requesttoquote/customer/po');
        return $resultRedirect;
    }
}
