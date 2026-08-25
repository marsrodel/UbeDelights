<?php
header('Content-Type: application/json');

$dataFile = __DIR__ . '/featured_data.json';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(['featured_ids' => []]));
}

$data = json_decode(file_get_contents($dataFile), true);
$featuredIds = isset($data['featured_ids']) ? $data['featured_ids'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = isset($input['product_id']) ? intval($input['product_id']) : 0;

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }

    $index = array_search($productId, $featuredIds);
    if ($index !== false) {
        array_splice($featuredIds, $index, 1);
    } else {
        if (count($featuredIds) >= 4) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Maximum 4 featured products allowed.']);
            exit;
        }
        $featuredIds[] = $productId;
    }

    $data['featured_ids'] = $featuredIds;
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => true,
        'featured_ids' => $featuredIds,
        'added' => $index === false,
        'product_id' => $productId
    ]);
} else {
    echo json_encode(['featured_ids' => $featuredIds]);
}
