<?php

namespace BlackBrickSoftware\CiviCRMContainerLogs;

use Civi\Api4\Entity;
use Civi\Core\LogManager;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

class Manager
{

    /**
     * List of registered log channels
     */
    private array $channels = [];

    /**
     * Find or create a logger.
     *
     * This implementation will look for a service "log.{NAME}". If none is
     * defined, then it will fallback to the "psr_log" service.
     *
     * @param string $channel
     *   Symbolic name of the intended log.
     *   This should correlate to a service "log.{NAME}".
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLog($channel = 'default'): LoggerInterface
    {
        try {

            if (isset($this->channels[$channel])) {
                return $this->channels[$channel];
            }

            if (!class_exists('\Monolog\Logger')) {
                throw new \RuntimeException('error triggered before stack fully loaded');
            }

            // Precedence: PHP constant `CIVICRM_CONTAINER_LOGS_LEVEL`
            // (e.g. `define(…)` in civicrm.settings.php) wins; otherwise
            // read the Civi setting (which itself resolves env var →
            // $civicrm_setting → DB → default).
            $levelName = defined('CIVICRM_CONTAINER_LOGS_LEVEL')
                ? (string) CIVICRM_CONTAINER_LOGS_LEVEL
                : (string) (\Civi::settings()->get('container_logs_level') ?: 'debug');
            $logLevel = Level::fromName($levelName);

            $logger = $this->getLogger($channel);

            $psrProcessor = new PsrLogMessageProcessor;
            $logger->pushProcessor($psrProcessor);

            $handler = new StreamHandler('php://stderr', $logLevel);

            $formatter = new JsonFormatter;
            $handler->setFormatter($formatter);

            $logger->pushHandler($handler);

            $this->channels[$channel] = $logger;

            return $this->channels[$channel];
            
        } catch (\Exception $e) {
            return $this->getBuiltInLogger($channel);
        }
    }

    /**
     * Get the channel name.
     *
     * This version of the name is intended for system wide use so we
     * include civicrm to disambiguation from other potential applications.
     *
     * @param string $channel
     *
     * @return string
     */
    protected function getChannelName(string $channel): string
    {
        return 'civicrm' . ($channel === 'default' ? '' : '.' . $channel);
    }

    /**
     * @param string $channel
     *
     * @return \Monolog\Logger
     */
    protected function getLogger(string $channel): Logger
    {
        return new Logger($this->getChannelName($channel));
    }

    /**
     * @param $channel
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function getBuiltInLogger($channel): LoggerInterface
    {
        $manager = new LogManager();
        return $manager->getLog($channel);
    }
}
