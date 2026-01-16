<?php

require_once 'container_logs.civix.php';

use CRM_ContainerLogs_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

// Load autoloader (if necessary)
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function container_logs_civicrm_config(&$config): void
{
    _container_logs_civix_civicrm_config($config);

    // Pre-register our Log_file class before PEAR Log can load the original
    // This intercepts all CRM_Core_Error::debug_log_message() calls
    if (!class_exists('Log_file', false)) {
        class_alias(
            \BlackBrickSoftware\CiviCRMContainerLogs\LogFile::class,
            'Log_file'
        );
    }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function container_logs_civicrm_install(): void
{
    _container_logs_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function container_logs_civicrm_enable(): void
{
    _container_logs_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_container().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container
 */
function container_logs_civicrm_container(ContainerBuilder $container): void
{
  // Replace the PSR log manager with our stderr-based implementation
  $container->setDefinition('psr_log_manager', new Definition('\BlackBrickSoftware\CiviCRMContainerLogs\Manager', []))->setPublic(true);

  // Register exception listener at high priority (before legacy handler at -200)
  // ref: https://docs.civicrm.org/dev/en/latest/hooks/usage/symfony/
  $container->findDefinition('dispatcher')
    ->addMethodCall('addListener', [
      'hook_civicrm_unhandled_exception',
      [\BlackBrickSoftware\CiviCRMContainerLogs\ExceptionHandler::class, 'handle'],
      100,
    ]);
}
