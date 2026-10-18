<?php
require_once __DIR__ . '/user_logger.php';

function logs_request_filters($request) {
    $filters = [];
    foreach (['search', 'role', 'date_from', 'date_to'] as $key) {
        if (isset($request[$key]) && !is_string($request[$key])) throw new InvalidArgumentException('Invalid log filters.');
        $filters[$key] = trim($request[$key] ?? '');
    }
    if (!in_array($filters['role'], ['', 'super_admin', 'admin', 'customer', 'system'], true)) {
        throw new InvalidArgumentException('Invalid role filter.');
    }
    foreach (['date_from', 'date_to'] as $key) {
        if ($filters[$key] === '') continue;
        $parts = explode('-', $filters[$key]);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$key]) || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            throw new InvalidArgumentException('Enter a valid date.');
        }
    }
    if ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']) {
        throw new InvalidArgumentException('From Date must be on or before To Date.');
    }
    foreach (['page', 'limit'] as $key) {
        if (isset($request[$key]) && !is_scalar($request[$key])) throw new InvalidArgumentException('Invalid pagination.');
    }
    $filters['page'] = max(1, (int)($request['page'] ?? 1));
    $filters['limit'] = max(1, min(100, (int)($request['limit'] ?? 10)));
    return $filters;
}

function logs_query_result($db, $sql, $types, $params) {
    $stmt = mysqli_prepare($db, $sql);
    if (!$stmt) throw new RuntimeException('Could not prepare log query.');
    try {
        if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
        if (!mysqli_stmt_execute($stmt)) throw new RuntimeException('Could not retrieve logs.');
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) throw new RuntimeException('Could not retrieve log results.');
        return $result;
    } finally {
        mysqli_stmt_close($stmt);
    }
}

function logs_fetch_page($db, $categories, $filters, $viewerRole, $viewerId = null) {
    $tables = ['system' => 'system_logs', 'authentication' => 'authentication_logs', 'activity' => 'activity_logs'];
    $sources = [];
    foreach ($categories as $category) {
        if (!isset($tables[$category])) throw new InvalidArgumentException('Invalid log type.');
        if ($viewerRole === 'customer' && $category === 'system') throw new InvalidArgumentException('Invalid log type.');
        $sources[] = "SELECT *, '$category' AS log_type FROM " . $tables[$category];
    }
    $source = '(' . implode(' UNION ALL ', $sources) . ') AS log_records';
    $where = [];
    $params = [];
    $types = '';
    if ($viewerRole === 'admin') {
        $where[] = "role IN ('admin', 'customer')";
    } elseif ($viewerRole === 'customer' && $viewerId) {
        $where[] = "idNumber = ? AND role = 'customer'";
        $params[] = $viewerId;
        $types .= 's';
    } elseif ($viewerRole !== 'super_admin') {
        throw new InvalidArgumentException('Unauthorized.');
    }
    if ($filters['search'] !== '') {
        $where[] = '(idNumber LIKE ? OR username LIKE ? OR fullName LIKE ? OR action LIKE ? OR module LIKE ? OR details LIKE ?)';
        $params = array_merge($params, array_fill(0, 6, '%' . $filters['search'] . '%'));
        $types .= 'ssssss';
    }
    if ($filters['role'] === 'system') {
        $where[] = 'role IS NULL';
    } elseif ($filters['role'] !== '') {
        $where[] = 'role = ?';
        $params[] = $filters['role'];
        $types .= 's';
    }
    if ($filters['date_from'] !== '') {
        $where[] = 'created_at >= ?';
        $params[] = $filters['date_from'] . ' 00:00:00';
        $types .= 's';
    }
    if ($filters['date_to'] !== '') {
        $where[] = 'created_at < DATE_ADD(?, INTERVAL 1 DAY)';
        $params[] = $filters['date_to'] . ' 00:00:00';
        $types .= 's';
    }
    $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $result = logs_query_result($db, "SELECT COUNT(*) AS total FROM $source$clause", $types, $params);
    $total = (int)mysqli_fetch_assoc($result)['total'];
    $totalPages = max(1, (int)ceil($total / $filters['limit']));
    $page = min($filters['page'], $totalPages);
    $fetchParams = array_merge($params, [$filters['limit'], ($page - 1) * $filters['limit']]);
    $result = logs_query_result($db, "SELECT *, DATE(created_at) AS log_date, TIME(created_at) AS log_time FROM $source$clause ORDER BY created_at DESC, id DESC, log_type ASC LIMIT ? OFFSET ?", $types . 'ii', $fetchParams);
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) $logs[] = $row;
    $logs = logs_attach_time_out($db, $logs);
    return [
        'success' => true,
        'logs' => $logs,
        'pagination' => ['page' => $page, 'limit' => $filters['limit'], 'total' => $total, 'totalPages' => $totalPages],
    ];
}
