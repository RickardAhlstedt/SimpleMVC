<?php

declare(strict_types=1);

namespace SimpleMVC\Service;

class LoggerService
{
    /**
     * @var string|null
     */
    private ?string $logFile;

    /**
     * LoggerService constructor.
     *
     * @param string|null $logFile Optional log file path. If not set, logs to stdout.
     */
    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile;
        if ($logFile && !file_exists($logFile)) {
            // Create the log file if it doesn't exist
            file_put_contents($logFile, '');
        }
    }

    public function log(string $message): void
    {
        $message = $this->messageToString($message);
        $formattedMessage = "[LOG] " . $message . PHP_EOL;
        if ($this->logFile) {
            file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        } else {
            echo $formattedMessage;
        }
    }

    public function info(string $message): void
    {
        $message = $this->messageToString($message);
        $formattedMessage = "[INFO] " . $message . PHP_EOL;
        if ($this->logFile) {
            file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        } else {
            echo $formattedMessage;
        }
    }

    public function error(string $message): void
    {
        $message = $this->messageToString($message);
        $formattedMessage = "[ERROR] " . $message . PHP_EOL;
        if ($this->logFile) {
            file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
        } else {
            echo $formattedMessage;
        }
    }

    private function messageToString(string $message): string
    {
        return is_string($message) ? $message : var_export($message, true);
    }
}
