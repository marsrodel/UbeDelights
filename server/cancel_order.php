<?php
header('Content-Type: application/json');
require_once __DIR__ . '/customer_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$orderId = intval($input['order_id']);
$userId  = $currentUser['id'];

$check = mysqli_query($connect, "SELECT status FROM orders WHERE order_id = $orderId AND user_id = '" . mysqli_real_escape_string($connect, $userId) . "'");

if (!$check || mysqli_num_rows($check) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$row = mysqli_fetch_assoc($check);
if ($row['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only pending orders can be cancelled']);
    exit;
}

$sql = "UPDATE orders SET status = 'cancelled' WHERE order_id = $orderId AND user_id = '" . mysqli_real_escape_string($connect, $userId) . "'";
if (mysqli_query($connect, $sql)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
}
