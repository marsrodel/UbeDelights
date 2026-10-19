<?php
session_start();
require_once __DIR__ . '/log_reader.php';
require_once __DIR__ . '/session_guard.php';
header('Content-Type: application/json');
session_guard_api_timeout();

if (!isset($_SESSION['auth_user_id']) || ($_SESSION['auth_role'] ?? '') !== 'customer' || ($_SESSION['auth_status'] ?? '') !== 'active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

session_guard_touch();

try {
    $filters = logs_request_filters($_GET);
    $filters['role'] = '';
    echo json_encode(logs_fetch_page($connect, ['activity', 'authentication'], $filters, 'customer', $_SESSION['auth_user_id']));
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    log_system_event('database_error', 'Personal account logs could not be retrieved.', 'Logs', 'ERROR', $_SESSION['auth_user_id'], $_SESSION['auth_username'] ?? null);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load logs. Please try again.']);
}
