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

## Log Level Configuration

You can control the minimum log level emitted by this extension using the `CIVICRM_CONTAINER_LOGS_LEVEL` constant. Set this constant in your environment or configuration to filter logs according to severity. The log level defaults to `debug`;

This constant accepts any value supported by [`\Monolog\Level`](https://seldaek.github.io/monolog/doc/01-usage.html#log-levels). Monolog supports the logging levels described by [RFC 5424](https://datatracker.ietf.org/doc/html/rfc5424).

 - **DEBUG (100):** Detailed debug information.
 - **INFO (200):** Interesting events. Examples: User logs in, SQL logs.
 - **NOTICE (250):** Normal but significant events.
 - **WARNING (300):** Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 - **ERROR (400):** Runtime errors that do not require immediate action but should typically be logged and monitored.
 - **CRITICAL (500):** Critical conditions. Example: Application component unavailable, unexpected exception.
 - **ALERT (550):** Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 - **EMERGENCY (600):** Emergency: system is unusable.

**Example usage:**

```php
define('CIVICRM_CONTAINER_LOGS_LEVEL', 'warning');
```

This will ensure only warnings and more severe messages are logged.

## Requirements

- PHP8.1 or newer
- CiviCRM installation
- Docker container environment (recommended)

## References
 - [Replace core logging with Monolog](https://lab.civicrm.org/extensions/monolog)
 - [CiviCRM Developer Guide: Logging](https://docs.civicrm.org/dev/en/latest/framework/logging/)