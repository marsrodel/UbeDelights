<?php
require_once __DIR__ . '/admin_auth.php';
header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$uploadDir = __DIR__ . '/../images/item_uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function uploadImage() {
    global $uploadDir;
    if (!isset($_FILES['productImage']) || $_FILES['productImage']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES['productImage'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return null;
    }
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
    $target = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'images/item_uploads/' . $filename;
    }
    return null;
}

function formatProduct($row) {
    return [
        'id'          => (int)$row['product_id'],
        'name'        => $row['name'],
        'price'       => '₱' . number_format((float)$row['price'], 0),
        'priceNum'    => (float)$row['price'],
        'category'    => $row['category'],
        'status'      => $row['status'] ?? '',
        'description' => $row['description'],
        'image'       => $row['image'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if ($action === 'add') {
    $name = isset($_POST['productName']) ? trim($_POST['productName']) : '';
    $desc = isset($_POST['productDesc']) ? trim($_POST['productDesc']) : '';
    $category = isset($_POST['productType']) ? trim($_POST['productType']) : '';
    $status = isset($_POST['productStatus']) ? trim($_POST['productStatus']) : null;
    $price = isset($_POST['productPrice']) ? floatval($_POST['productPrice']) : 0;

    if ($name === '' || $desc === '' || $category === '' || $price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    $imagePath = uploadImage();
    if (!$imagePath) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please select a valid product image.']);
        exit;
    }

    $sql = "INSERT INTO products (name, price, category, status, description, image) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 'sdssss', $name, $price, $category, $status, $desc, $imagePath);
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($connect);
            mysqli_stmt_close($stmt);
            $sql2 = "SELECT * FROM products WHERE product_id = ?";
            if ($stmt2 = mysqli_prepare($connect, $sql2)) {
                mysqli_stmt_bind_param($stmt2, 'i', $newId);
                mysqli_stmt_execute($stmt2);
                $res = mysqli_stmt_get_result($stmt2);
                $row = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt2);
                echo json_encode(['success' => true, 'product' => formatProduct($row)]);
            }
        } else {
            mysqli_stmt_close($stmt);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add product.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }

} elseif ($action === 'edit') {
    $id = isset($_POST['productId']) ? intval($_POST['productId']) : 0;
    $name = isset($_POST['productName']) ? trim($_POST['productName']) : '';
    $desc = isset($_POST['productDesc']) ? trim($_POST['productDesc']) : '';
    $category = isset($_POST['productType']) ? trim($_POST['productType']) : '';
    $status = isset($_POST['productStatus']) ? trim($_POST['productStatus']) : null;
    $price = isset($_POST['productPrice']) ? floatval($_POST['productPrice']) : 0;

    if ($id <= 0 || $name === '' || $desc === '' || $category === '' || $price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    $hasNewImage = isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK;
    if ($hasNewImage) {
        $imagePath = uploadImage();
        if (!$imagePath) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid image file.']);
            exit;
        }
        $sql = "UPDATE products SET name=?, price=?, category=?, status=?, description=?, image=? WHERE product_id=?";
        if ($stmt = mysqli_prepare($connect, $sql)) {
            mysqli_stmt_bind_param($stmt, 'sdssssi', $name, $price, $category, $status, $desc, $imagePath, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "UPDATE products SET name=?, price=?, category=?, status=?, description=? WHERE product_id=?";
        if ($stmt = mysqli_prepare($connect, $sql)) {
            mysqli_stmt_bind_param($stmt, 'sdsssi', $name, $price, $category, $status, $desc, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $sql3 = "SELECT * FROM products WHERE product_id = ?";
    if ($stmt3 = mysqli_prepare($connect, $sql3)) {
        mysqli_stmt_bind_param($stmt3, 'i', $id);
        mysqli_stmt_execute($stmt3);
        $res = mysqli_stmt_get_result($stmt3);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt3);
        if ($row) {
            echo json_encode(['success' => true, 'product' => formatProduct($row)]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
        }
    }

} elseif ($action === 'delete') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }

    $sql = "SELECT image FROM products WHERE product_id = ?";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if ($row && $row['image']) {
            $imgFile = __DIR__ . '/../' . $row['image'];
            if (file_exists($imgFile)) {
                unlink($imgFile);
            }
        }
    }

    $sql2 = "DELETE FROM products WHERE product_id = ?";
    if ($stmt2 = mysqli_prepare($connect, $sql2)) {
        mysqli_stmt_bind_param($stmt2, 'i', $id);
        if (mysqli_stmt_execute($stmt2)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
        }
        mysqli_stmt_close($stmt2);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
