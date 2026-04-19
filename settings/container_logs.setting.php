<?php

use CRM_ContainerLogs_ExtensionUtil as E;

/**
 * Minimum PSR-3 level written to stderr.
 */
return [
  'container_logs_level' => [
    'name' => 'container_logs_level',
    'type' => 'String',
    'default' => 'debug',
    'html_type' => 'select',
    'title' => E::ts('Container Logs Minimum Level'),
    'description' => E::ts('Lowest PSR-3 level written to stderr: debug, info, notice, warning, error, critical, alert, emergency.'),
    'is_domain' => 1,
    'is_contact' => 0,
    'is_constant' => TRUE,
    'is_env_loadable' => TRUE,
    'global_name' => 'CIVICRM_CONTAINER_LOGS_LEVEL',
  ],
];
