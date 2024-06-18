<?php

namespace OX\Auction\ViewModel;

use Magento\Framework\Escaper;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PrivateAuctionForm implements ArgumentInterface
{
    /**
     * @var Escaper
     */
    public $escaper;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * PrivateAuctionForm constructor.
     *
     * @param SerializerInterface $serializer
     * @param Escaper $escaper
     */
    public function __construct(
        SerializerInterface $serializer,
        Escaper $escaper
    ) {
        $this->serializer = $serializer;
        $this->escaper = $escaper;
    }

    /**
     * Unserialize the customer ids
     *
     * @param string $string
     * @return array|bool|float|int|string|null
     */
    public function unserialize($string)
    {
        return $this->serializer->unserialize($string);
    }

    /**
     * Concatenate Customer Detail for Option
     *
     * @param object $customer
     * @return string
     */
    public function getDtlCustOpt($customer)
    {
        $data = $customer->getId() . ',';
        $data .= $customer->getFirstname() . ',';
        $data .= $customer->getLastname() . ',';
        $data .= $customer->getEmail() . ',';
        return $data;
    }
}
