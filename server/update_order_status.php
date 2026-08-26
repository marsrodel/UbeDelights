<?php
header('Content-Type: application/json');
require_once __DIR__ . '/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id or status']);
    exit;
}

$orderId = intval($input['order_id']);
$status  = $input['status'];

$validStatuses = ['pending', 'confirmed', 'delivered', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

$sql = "UPDATE orders SET status = ? WHERE order_id = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update order']);
}

mysqli_stmt_close($stmt);
