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
namespace Ced\RequestToQuote\Controller\Quotes;

use Ced\RequestToQuote\Model\RequestQuoteFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class DeleteValue extends Action
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var RequestQuoteFactory
     */
    protected $requestQuoteFactory;

    /**
     * @param Context $context
     * @param Session $session
     * @param RequestQuoteFactory $requestQuoteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $session,
        RequestQuoteFactory $requestQuoteFactory,
        array $data = []
    ) {
        $this->requestQuoteFactory = $requestQuoteFactory;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (! $this->session->isLoggedIn()) {
            $this->messageManager->addErrorMessage(__('Please login first'));
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        if ($id = $this->getRequest()->getParam('id')) {
            $item = $this->requestQuoteFactory->create()->load($id);
            if ($item && $item->getId()) {
                $item->delete();
            }
        }
        $this->messageManager->addSuccessMessage(__('Item was deleted successfully'));
        $resultRedirect->setPath('requesttoquote/cart/index');
        return $resultRedirect;
    }
}
