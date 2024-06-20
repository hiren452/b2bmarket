<?php
namespace Matrix\NoncatalogueRfq\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/noncatalogrfq.log';
}
