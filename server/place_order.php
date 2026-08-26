<?php
header('Content-Type: application/json');
require_once __DIR__ . '/customer_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['address']) || !isset($input['items']) || !is_array($input['items']) || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

$addr = $input['address'];
$name     = isset($addr['firstName']) ? trim($addr['firstName']) : '';
$lastName = isset($addr['lastName']) ? trim($addr['lastName']) : '';
$email    = isset($addr['email']) ? trim($addr['email']) : '';
$phone    = isset($addr['phone']) ? trim($addr['phone']) : '';
$street   = isset($addr['street']) ? trim($addr['street']) : '';
$barangay = isset($addr['barangay']) ? trim($addr['barangay']) : '';
$city     = isset($addr['city']) ? trim($addr['city']) : '';
$province = isset($addr['province']) ? trim($addr['province']) : '';
$zip      = isset($addr['zip']) ? trim($addr['zip']) : '';

$customerName = $name . ' ' . $lastName;

$notes = isset($input['notes']) ? trim($input['notes']) : '';

$subtotal = 0;
foreach ($input['items'] as $item) {
    $price = floatval(str_replace(['₱', ','], '', $item['price']));
    $qty   = intval($item['qty']);
    $subtotal += $price * $qty;
}

$shipping = $subtotal >= 500 ? 0 : 99;
$total    = $subtotal + $shipping;
$userId   = $currentUser['id'];

	mysqli_begin_transaction($connect);

try {
    $sql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, street, barangay, city, province, zip_code, subtotal, shipping_fee, total_amount, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssssssssdds',
        $userId, $customerName, $email, $phone,
        $street, $barangay, $city, $province, $zip,
        $subtotal, $shipping, $total, $notes
    );
    mysqli_stmt_execute($stmt);
    $orderId = mysqli_insert_id($connect);
    mysqli_stmt_close($stmt);

    $itemSql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?, ?)";
    $itemStmt = mysqli_prepare($connect, $itemSql);

    foreach ($input['items'] as $item) {
        $productId   = isset($item['id']) ? intval($item['id']) : null;
        $productName = $item['name'];
        $price       = floatval(str_replace(['₱', ','], '', $item['price']));
        $qty         = intval($item['qty']);
        $lineTotal   = $price * $qty;

        mysqli_stmt_bind_param($itemStmt, 'iisidd',
            $orderId, $productId, $productName, $qty, $price, $lineTotal
        );
        mysqli_stmt_execute($itemStmt);
    }
    mysqli_stmt_close($itemStmt);

    mysqli_commit($connect);

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    mysqli_rollback($connect);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to place order']);
}
