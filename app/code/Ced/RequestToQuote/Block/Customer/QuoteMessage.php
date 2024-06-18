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

namespace Ced\RequestToQuote\Block\Customer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

/**
 * Class QuoteMessage
 * @package Ced\RequestToQuote\Block\Customer
 */
class QuoteMessage extends Template
{

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * QuoteMessage constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->session = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCurrentQuoteHasProposalItems()
    {
        $proposalId = 0;
        if ($this->session->isLoggedIn()) {
            $quoteItems = $this->checkoutSession->getQuote()->getAllItems();
            if (count($quoteItems)) {
                foreach ($quoteItems as $item) {
                    if ((int)$proposalId = $item->getCedPoId()) {
                        break;
                    }
                }
            }
        }
        return $proposalId;
    }
}
