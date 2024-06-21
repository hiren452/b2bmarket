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

namespace Ced\CsMembership\Controller\Adminhtml\Membership;

use Magento\Backend\App\Action\Context;

/**
 * Class Delete (for deleting membership plan)
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsMembership\Model\MembershipFactory
     */
    protected $membershipFactory;

    /**
     * Delete constructor.
     * @param \Ced\CsMembership\Model\MembershipFactory $membershipFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Ced\CsMembership\Model\MembershipFactory $membershipFactory,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context);
        $this->membershipFactory = $membershipFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->getRequest()->getParam("id") > 0) {
            try {
                $model = $this->membershipFactory->create();
                $product_id = $model->load($this->getRequest()->getParam("id"))->getProductId();
                $model->setId($this->getRequest()->getParam("id"))->delete();
                $this->_eventManager->dispatch('delete_membership_virtual_product', ['product' => $product_id]);
                $this->messageManager->addSuccessMessage(__('Membership is successfully deleted.'));
                $this->_redirect("*/*/");
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $this->_redirect("*/*/edit", ["id" => $this->getRequest()->getParam("id")]);
            }
        }
        $this->_redirect("*/*/");
    }
}
