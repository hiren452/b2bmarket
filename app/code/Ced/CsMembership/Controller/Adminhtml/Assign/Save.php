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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Controller\Adminhtml\Assign;

/**
 * Class Save (assigning membership)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * @var \Ced\CsMembership\Helper\Data
     */
    protected $membershipHelper;

    /**
     * Save constructor.
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param \Ced\CsMembership\Helper\Data $membershipHelper
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        \Ced\CsMembership\Helper\Data $membershipHelper,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->membershipFactory = $membershipFactory;
        $this->membershipHelper = $membershipHelper;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $post_data = $this->getRequest()->getPostValue();
        if (isset($post_data['selected_vendor'])) {
            $membershipId = $this->getRequest()->getParam('id');
            $selectedVendors = $this->getRequest()->getParam('selected_vendor');
            $qtyModel = $this->membershipFactory->create()->load($membershipId);
            $prvqty = $qtyModel->getQty();
            if ($prvqty >= count($selectedVendors)) {
                $result = $this->membershipHelper->assignMembership($membershipId, $selectedVendors);
                if ($result) {
                    $qtyModel = $this->membershipFactory->create()->load($membershipId);
                    $prvqty = $qtyModel->getQty();
                    $newqty = $prvqty - count($selectedVendors);
                    $qtyModel->setQty($newqty);
                    $qtyModel->save();
                    $this->messageManager->addSuccessMessage(__("Package successfully assigned to Vendors."));
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addErrorMessage(__("Package is not Sufficient for Selected Qty of Vendors."));
                $this->_redirect('*/*/');
            }
        } else {
            $this->messageManager->addErrorMessage(__("Please select one or more vendors."));
            $this->_redirect("*/*/");
        }
    }
}
