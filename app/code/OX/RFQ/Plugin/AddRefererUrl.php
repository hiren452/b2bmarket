<?php

namespace OX\RFQ\Plugin;

use Ced\RequestToQuote\Helper\Data;
use Magento\Framework\UrlInterface;

class AddRefererUrl
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * AddRefererUrl constructor.
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Around plugin to add referer url
     *
     * @param Data $subject
     * @param callable $proceed
     */
    public function aroundGetSeePriceHtml(Data $subject, callable $proceed)
    {
        $html = '<div class="price">';
        $html .= '<a href="' . $this->getLoginUrl() . '">';
        $html .= $subject->getSeePriceMessage();
        $html .= '</a>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Add Referer url to login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->urlBuilder
            ->getUrl('customer/account/login', ['referer' => base64_encode($this
                ->urlBuilder->getCurrentUrl())]);
    }
}
