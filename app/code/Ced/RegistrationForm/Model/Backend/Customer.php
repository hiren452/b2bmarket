<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\RegistrationForm\Model\Backend;

/**
 * Plugin for image block
 */
class Customer extends \Magento\Customer\Model\Backend\Customer
{
    public function getCertificateUrl()
    {
        $url = false;
        $certificate_pdf = $this->getDocuments();
        if ($certificate_pdf) {
            if (is_string($certificate_pdf)) {
                $url = $this->_storeManager->getStore()->getBaseUrl() . 'pub/media/catalog/category/' . $certificate_pdf;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('something went wrong')
                );
            }
        }
        return $url;
    }
}
