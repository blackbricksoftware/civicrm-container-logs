<?php

namespace BlackBrickSoftware\CiviCRMContainerLogs;

use Civi\Core\Event\UnhandledExceptionEvent;

/**
 * Handles CiviCRM exceptions by logging them to stderr.
 *
 * This captures exceptions that would otherwise only be written to file
 * by CRM_Core_Error::handleUnhandledException().
 */
class ExceptionHandler
{
    /**
     * Log an unhandled exception to stderr.
     */
    public static function handle(UnhandledExceptionEvent $event): void
    {
        $exception = $event->exception;

        try {
            \Civi::log('exception')->error($exception->getMessage(), [
                'exception_class' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        } catch (\Throwable $e) {
            self::fallbackLog($exception);
        }
    }

    /**
     * Direct stderr fallback when Monolog isn't available.
     */
    private static function fallbackLog(\Throwable $exception): void
    {
        $json = json_encode([
            'message' => $exception->getMessage(),
            'context' => [
                'exception_class' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
            'level' => 400,
            'level_name' => 'ERROR',
            'channel' => 'civicrm.exception',
            'datetime' => date('c'),
            'extra' => new \stdClass(),
        ]);
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $json . "\n");
        fclose($stderr);
    }
}
