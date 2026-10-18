<?php

function log_runtime_failure($action, $details, $severity) {
    static $reporting = false;
    if ($reporting) return;
    $reporting = true;
    try {
        require_once __DIR__ . '/user_logger.php';
        log_system_event($action, $details, 'Application', $severity, $_SESSION['auth_user_id'] ?? null, $_SESSION['auth_username'] ?? null);
    } catch (Throwable $e) {
        error_log('Ube Delights could not record a system failure.');
    } finally {
        $reporting = false;
    }
}

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    if (in_array($severity, [E_WARNING, E_USER_WARNING, E_RECOVERABLE_ERROR], true)) {
        $action = stripos($message, 'mysqli') !== false ? 'database_error' : 'application_error';
        log_runtime_failure($action, 'A PHP warning occurred in ' . basename($file) . ' at line ' . $line . '.', 'WARNING');
    }
    return false;
});

register_shutdown_function(function () {
    $last = error_get_last();
    if (!$last || !in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) return;
    $action = stripos($last['message'], 'mysqli') !== false ? 'database_error' : 'application_error';
    log_runtime_failure($action, 'A fatal error occurred in ' . basename($last['file']) . ' at line ' . $last['line'] . '.', 'CRITICAL');
});
