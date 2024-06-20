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
 * @package     Ced_CsMultistepreg
 * @author      CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license     https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Controller\Adminhtml\Attributes;

class SaveSteps extends \Magento\Backend\App\Action
{
    /**
     * @var \Ced\CsMultistepreg\Model\Steps
     */
    protected $steps;

    /**
     * SaveSteps constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ced\CsMultistepreg\Model\Steps $steps
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ced\CsMultistepreg\Model\Steps $steps
    ) {
        parent::__construct($context);
        $this->steps = $steps;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();

        $steps = $postData['steps']['options']['label'];
        $count = 0;
        $stepsCollection = $this->steps->getCollection();
        $stepsCollection->walk('delete');
        try {
            if (!$steps) {
                $this->messageManager->addSuccessMessage(__('Action Performed Successfully'));
                return $this->_redirect('*/*/newStep');
            }

            foreach ($steps as $step) {
                $this->steps->setStepNumber($step['step_number']);
                $this->steps->setStepLabel($step['step_label']);
                $this->steps->save();
                $this->steps->unsetData();
                $count++;
            }
            $this->messageManager->addSuccessMessage(__($count . ' Steps Has Been Saved.'));
            return $this->_redirect('*/*/newStep');
        } catch (\Exception $e) {
            $this->messageManager->addSuccessMessage(__($e->getMessage()));
            return $this->_redirect('*/*/newStep');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
