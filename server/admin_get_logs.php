<?php
session_start();
require_once __DIR__ . '/log_reader.php';
require_once __DIR__ . '/session_guard.php';
header('Content-Type: application/json');
session_guard_api_timeout();

if (!isset($_SESSION['auth_user_id']) || !in_array($_SESSION['auth_role'] ?? '', ['admin', 'super_admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
if (($_SESSION['auth_status'] ?? '') !== 'active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account not active']);
    exit();
}

session_guard_touch();

try {
    $type = $_GET['type'] ?? 'authentication';
    if (!is_string($type) || !in_array($type, ['system', 'authentication', 'activity'], true)) {
        throw new InvalidArgumentException('Invalid log type.');
    }
    $response = logs_fetch_page($connect, [$type], logs_request_filters($_GET), $_SESSION['auth_role']);
    if ($type === 'activity') {
        $requesterIdsByRequest = [];
        $requestIdsByRow = [];
        foreach ($response['logs'] as $index => $log) {
            if (strtolower((string)$log['action']) !== 'deletion_review') continue;
            $metadata = log_deletion_event_metadata($log['details']);
            $requestId = (int)($metadata['request_id'] ?? 0);
            $requesterId = (string)($metadata['requested_by_id'] ?? '');
            if ($requestId <= 0 || $requesterId === '' || !empty($metadata['requested_by_full_name'])) continue;
            $requesterIdsByRequest[$requestId] = $requesterId;
            $requestIdsByRow[$index] = $requestId;
        }
        $requesterNames = log_deletion_requester_names($connect, $requesterIdsByRequest);
        foreach ($requestIdsByRow as $index => $requestId) {
            if (isset($requesterNames[$requestId])) {
                $response['logs'][$index]['requestedByFullName'] = $requesterNames[$requestId];
            }
        }
    }
    $response['type'] = $type;
    echo json_encode($response);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    log_system_event('database_error', 'The requested logs could not be retrieved.', 'Logs', 'ERROR', $_SESSION['auth_user_id'], $_SESSION['auth_username'] ?? null);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load logs. Please try again.']);
}
