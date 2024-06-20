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
 * @package     Ced_CsMultistepregistration
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMultistepreg\Controller\Multistep;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $manager;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Event\ManagerInterface $manager
     */
    public function __construct(
        Context $context,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Magento\Framework\Event\ManagerInterface $manager
    ) {
        parent::__construct($context);
        $this->vendorFactory = $vendorFactory;
        $this->manager = $manager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $vendorId = $this->getRequest()->getParam('vendor_id');
        if ($vendorId) {
            $vendor = $this->vendorFactory->create()->load($vendorId);
            $vendor->addData($this->getRequest()->getParam('vendor'));
            try {
                $vendor->save();
                $this->manager->dispatch('vendor_multistepregistration_complete_after', ['vendor'=>$vendor]);
                return $this->_redirect('csmarketplace/account/approval');
            } catch (\Exception $e) {
                throw new \Exception(__($e->getMessage()));
            }
        }
    }
}
