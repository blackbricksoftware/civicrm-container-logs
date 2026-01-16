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
            $manager = \Civi::service('psr_log_manager');
            $logger = $manager->getLog('exception');

            $logger->error($exception->getMessage(), [
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
            'level' => 'error',
            'channel' => 'civicrm.exception',
            'message' => $exception->getMessage(),
            'context' => [
                'exception_class' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ],
        ]);
        fwrite(STDERR, $json . "\n");
    }
}
