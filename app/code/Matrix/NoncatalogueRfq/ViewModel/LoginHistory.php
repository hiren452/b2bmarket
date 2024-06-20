<?php

namespace Matrix\NoncatalogueRfq\ViewModel;

use Magento\Framework\Session\SessionManagerInterface;

class LoginHistory implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    protected $_sessionManager;

    public function __construct(
        SessionManagerInterface $session,
        \Matrix\NoncatalogueRfq\Model\Session $noncatrfqSession
    ) {
        $this->_sessionManager = $session;
        $this->noncatrfqsession = $noncatrfqSession;
    }
    public function getSessionData()
    {

        $gotSession= $this->noncatrfqsession->getData('nonCatrRqPopupPostData');
        if ($gotSession) {
            $decoded = json_decode($gotSession, true);
            return $decoded['email-rfqpopup'];
        }
    }
}
