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

class Validate extends \Magento\Backend\App\Action
{

    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        if ($this->getRequest()->isAjax()) {

            $data = $this->getRequest()->getPost();
            $steps = $data['steps']['options']['label'];
            $requestSteps = [];
            $validationPassed = true;
            $labelValidation = true;
            $error = '';
            if (!$steps) {
                $response->setError(false);
                $response->setError(true);
                $response->setMessage(__('Atleast One Step Is Required For Multi-Step Registration Form. Steps Could Not Be Deleted'));

                return $this->resultJsonFactory->create()->setJsonData($response->toJson());

            }
            foreach ($steps as $step) {
                $requestSteps[] = $step['step_number'];
                if (!is_numeric($step['step_number'])) {
                    $error .= $step['step_number'];
                    $validationPassed = false;
                }
                if ($step['step_label'] == '') {
                    $labelValidation = false;
                }
                if ($step['step_number'] <= 0) {
                    $error .= $step['step_number'] . __(' is not a valid step number');
                    $validationPassed = false;
                }
            }
            if (count($requestSteps) != count(array_unique($requestSteps))) {
                $response->setError(true);
                $response->setMessage(__('Step Numbers must be unique'));
            } elseif (!$validationPassed) {
                $response->setError(true);
                $response->setMessage(__('Step Numbers must be a valid number ' . $error));
            } elseif (!$labelValidation) {
                $response->setError(true);
                $response->setMessage(__('Step Labels must be a valid text'));
            } else {
                $response->setError(false);
                $response->setMessage(__('Validated Successfully'));
            }
            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }
    }
}
