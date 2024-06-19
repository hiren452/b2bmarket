<?php

namespace OX\RFQ\Plugin;

use Ced\RequestToQuote\Helper\Data;

class RFQProposal
{
    /**
     * The method is used to send empty string as bool affecting the success message
     *
     * @param Data $subject
     * @param bool $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSendPoCreatedMail(
        Data $subject,
        bool $result
    ): string {
        return "";
    }
}
