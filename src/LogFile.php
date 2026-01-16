<?php

namespace BlackBrickSoftware\CiviCRMContainerLogs;

use Psr\Log\LoggerInterface;

/**
 * Custom PEAR Log_file replacement that writes to stderr.
 *
 * This class is aliased to 'Log_file' before PEAR's Log::factory()
 * can load the original, intercepting all file-based logging.
 */
class LogFile extends \Log
{
    private string $filename;
    private string $timeFormat = 'Y-m-d H:i:sO';

    public function __construct(
        string $name,
        string $ident = '',
        array $conf = [],
        int $level = PEAR_LOG_DEBUG
    ) {
        $this->id = md5(microtime() . random_int(0, mt_getrandmax()));
        $this->filename = $name;
        $this->ident = $ident;
        $this->mask = \Log::MAX($level);

        if (!empty($conf['timeFormat'])) {
            $this->timeFormat = $conf['timeFormat'];
        }
    }

    /**
     * Destructor (no-op for stderr).
     */
    public function log_file_destructor(): void
    {
        if ($this->opened) {
            $this->close();
        }
    }

    /**
     * Open the log (no-op for stderr).
     */
    public function open(): bool
    {
        $this->opened = true;
        return true;
    }

    /**
     * Close the log (no-op for stderr).
     */
    public function close(): bool
    {
        $this->opened = false;
        return true;
    }

    /**
     * Flush the log (no-op for stderr).
     */
    public function flush(): bool
    {
        return true;
    }

    /**
     * Log a message.
     *
     * CiviCRM sometimes passes string priorities (e.g., 'error') instead of
     * PEAR_LOG_* constants, so we handle both types.
     *
     * @param mixed $message The message to log
     * @param int|string|null $priority Priority level (PEAR_LOG_* constant or string name)
     */
    public function log($message, ?int $priority = null): bool
    {
        // Convert string priority to PEAR constant using parent's method
        if (is_string($priority)) {
            $priority = $this->stringToPriority($priority);
        }

        if ($priority === null) {
            $priority = $this->priority;
        }

        if (!$this->isMasked($priority)) {
            return false;
        }

        $message = $this->extractMessage($message);
        $level = $this->priorityToLevel($priority);
        $context = [
            'ident' => $this->ident,
            'file' => basename($this->filename),
        ];

        try {
            $logger = $this->getLogger();
            $logger->log($level, $message, $context);
        } catch (\Throwable $e) {
            $this->fallbackLog($message, $level, $context);
        }

        $this->announce(['priority' => $priority, 'message' => $message]);

        return true;
    }

    /**
     * Get the Monolog logger via Civi::log().
     */
    private function getLogger(): LoggerInterface
    {
        return \Civi::log('debug');
    }

    /**
     * Direct stderr fallback when Monolog isn't available.
     */
    private function fallbackLog(string $message, string $level, array $context): void
    {
        $json = json_encode([
            'message' => $message,
            'context' => $context,
            'level' => $this->levelToMonologCode($level),
            'level_name' => strtoupper($level),
            'channel' => 'civicrm.debug',
            'datetime' => date('c'),
            'extra' => new \stdClass(),
        ]);
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $json . "\n");
        fclose($stderr);
    }

    private function levelToMonologCode(string $level): int
    {
        return match($level) {
            'debug' => 100,
            'info' => 200,
            'notice' => 250,
            'warning' => 300,
            'error' => 400,
            'critical' => 500,
            'alert' => 550,
            'emergency' => 600,
            default => 200,
        };
    }

    private function priorityToLevel(int $priority): string
    {
        return match($priority) {
            PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT => 'critical',
            PEAR_LOG_ERR => 'error',
            PEAR_LOG_WARNING => 'warning',
            PEAR_LOG_NOTICE => 'notice',
            PEAR_LOG_INFO => 'info',
            PEAR_LOG_DEBUG => 'debug',
            default => 'info',
        };
    }
}
