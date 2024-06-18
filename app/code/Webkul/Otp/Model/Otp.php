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
namespace Webkul\Otp\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Webkul\Otp\Api\Data\OtpInterface;

/**
 * Otp Model Class Of Otp Module
 */
class Otp extends \Magento\Framework\Model\AbstractModel implements OtpInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * otp cache tag.
     */
    public const CACHE_TAG = 'wk_Otp';

    /**
     * @var string
     */
    private $cacheTag = 'wk_Otp';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    private $eventPrefix = 'wk_otp';

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init(\Webkul\Otp\Model\ResourceModel\Otp::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteGallery();
        }
        return parent::load($id, $field);
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Email.
     *
     * @return string
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * Set Email.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get OTP.
     *
     * @return int
     */
    public function getOtp()
    {
        return parent::getData(self::OTP);
    }

    /**
     * Set OTP.
     *
     * @param int $otp
     */
    public function setOtp($otp)
    {
        return $this->setData(self::OTP, $otp);
    }

    /**
     * Set Created At
     *
     * @param  int $created_at
     * @return void
     */
    public function setCreatedAt($created_at)
    {
        return $this->setData(self::CREATED_AT, $created_at);
    }

    /**
     * Get CreatedAt
     *
     * @return date
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }
}
