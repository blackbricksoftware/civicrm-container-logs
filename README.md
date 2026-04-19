# CiviCRM Extension: Container Logging

This extension replaces the default CiviCRM log channels to send all logs to `stderr` (currently). This is especially useful when running CiviCRM inside a Docker container, as it allows logs to be consolidated and managed by the container runtime.

## Features

- Redirects all CiviCRM logs to `stderr`
- Simplifies log management in containerized environments

## Usage

1. Install the extension in your CiviCRM installation.
2. Enable the extension from the CiviCRM admin interface.
3. Logs will now appear in the container's standard error output.

## Configuration

### Log Level Configuration

The minimum log level can be set three ways, highest precedence first:

1. **PHP constant** in `civicrm.settings.php`:
   `define('CIVICRM_CONTAINER_LOGS_LEVEL', 'warning');`
2. **Environment variable** (recommended for containerised deployments):
   `CIVICRM_CONTAINER_LOGS_LEVEL=warning` — picked up natively by CiviCRM's
   `SettingsManager` because the setting metadata declares
   `is_env_loadable: TRUE`, `global_name: 'CIVICRM_CONTAINER_LOGS_LEVEL'`.
3. **`$civicrm_setting` in `civicrm.settings.php`**:
   `$civicrm_setting['domain']['container_logs_level'] = 'warning';`

The setting is declared `is_constant: TRUE`, so attempts to write via
`Civi::settings()->set(...)` / the API / the admin UI are rejected with
a helpful error pointing to the three supported sources above.

Default: `debug` (everything passes through).

Accepted values are any Monolog level name (see [`\Monolog\Level`](https://seldaek.github.io/monolog/doc/01-usage.html#log-levels); values from [RFC 5424](https://datatracker.ietf.org/doc/html/rfc5424)):

 - **DEBUG (100)**, **INFO (200)**, **NOTICE (250)**, **WARNING (300)**,
 - **ERROR (400)**, **CRITICAL (500)**, **ALERT (550)**, **EMERGENCY (600)**.

## Requirements

- PHP8.1 or newer
- CiviCRM installation
- Docker container environment (recommended)

## References
 - [Replace core logging with Monolog](https://lab.civicrm.org/extensions/monolog)
 - [CiviCRM Developer Guide: Logging](https://docs.civicrm.org/dev/en/latest/framework/logging/)