<?php namespace Scoby\Analytics;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{

    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        error_log(
            sprintf(
                '%s: %s. Details: %s',
                $level,
                trim($message, '.'),
                json_encode($context)
            )
        );
        
    }

    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
}
