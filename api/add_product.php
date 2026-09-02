<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8");

$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare("INSERT INTO products (product_name, sku, category_id, supplier_id, cost_price, selling_price, stock_quantity, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->execute([
    $data['product_name'],
    $data['sku'],
    $data['category_id'],
    $data['supplier_id'],
    $data['cost_price'],
    $data['selling_price'],
    $data['stock_quantity']
]);

echo json_encode(['success'=>true]);
