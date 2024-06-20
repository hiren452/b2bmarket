<?php
namespace Matrix\NoncatalogueRfq\Logger;

class Logger extends \Monolog\Logger
{

    protected $_scopeConfig;

    public function __construct(
        $name,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $handlers = [],
        array $processors = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($name, $handlers, $processors);
    }

    public function info($message, array $context = []):void
    {
        $isDebugMode = $this->isDebugMode('noncatalogrfq_configuration/active/debugmode');
        $isDebugMode = true;
        if ($isDebugMode) {
            // return $this->addRecord(static::INFO, $message, $context);
            parent::info($message, $context);
        }

        return;
    }

    public function notice($message, array $context = []):void
    {
        $isDebugMode = $this->isDebugMode('noncatalogrfq_configuration/active/debugmode');
        $isDebugMode = true;
        if ($isDebugMode) {
            //return $this->addRecord(static::NOTICE, $message, $context);
            parent::info($message, $context);
        }
        return;
    }

    public function warning($message, array $context = []):void
    {
        $isDebugMode = $this->isDebugMode('noncatalogrfq_configuration/active/debugmode');
        $isDebugMode = true;
        if ($isDebugMode) {
            //return $this->addRecord(static::WARNING, $message, $context);
            parent::info($message, $context);
        }
        return;
    }

    public function err($message, array $context = []):void
    {
        $isDebugMode = $this->isDebugMode('noncatalogrfq_configuration/active/debugmode');
        $isDebugMode = true;
        if ($isDebugMode) {
            //return $this->addRecord(static::ERROR, $message, $context);
            parent::info($message, $context);
        }
        return;
    }

    public function warn($message, array $context = []):void
    {
        $isDebugMode = $this->isDebugMode('noncatalogrfq_configuration/active/debugmode');
        $isDebugMode = true;
        if ($isDebugMode) {
            //return $this->addRecord(static::WARNING, $message, $context);
            parent::info($message, $context);
        }
        return;
    }

    public function isDebugMode($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
