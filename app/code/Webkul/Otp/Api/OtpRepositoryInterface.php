<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Otp\Api;

/**
 * @api
 */
interface OtpRepositoryInterface
{
    /**
     * Save Otp.
     *
     * @param  \Webkul\Otp\Api\Data\OtpInterface $otp
     * @return \Webkul\Otp\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Otp\Api\Data\OtpInterface $otp);

    /**
     * Retrieve Otp.
     *
     * @param  string $customerEmail
     * @return \Webkul\Otp\Api\Data\OtpInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByEmail($customerEmail);

    /**
     * Delete Otp Data.
     *
     * @param  string $customerEmail
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByEmail($customerEmail);
}
