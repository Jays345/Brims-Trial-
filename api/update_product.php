<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8");

$id = $_GET['id'] ?? 0;
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $pdo->prepare("UPDATE products SET product_name=?, sku=?, category_id=?, supplier_id=?, cost_price=?, selling_price=?, stock_quantity=? WHERE product_id=?");
$stmt->execute([
    $data['product_name'],
    $data['sku'],
    $data['category_id'],
    $data['supplier_id'],
    $data['cost_price'],
    $data['selling_price'],
    $data['stock_quantity'],
    $id
]);

echo json_encode(['success'=>true]);
