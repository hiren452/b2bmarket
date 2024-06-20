<?php

namespace Matrix\NoncatalogueRfq\Plugin;

class Topmenu
{
    /**
     * @param Context   $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $session
    ) {
        $this->Session = $session;
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {

        $html = "<li class=\"level0 nav-4 level-top parent ui-menu-item \">";
        $html .= "<a id=\"noncatalorrfq-post\" href=\"javascript:void()\" class=\"level-top ui-corner-all\"><span class=\"ui-menu-icon ui-icon ui-icon-carat-1-e\"></span><span>" . __("POST RFQ") . "</span></a>";
        $html .= "</li>";
        return $html;
    }
}
