<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8");

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$supplier = $_GET['supplier'] ?? '';

$sql = "SELECT p.*, c.category_name, s.supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id=c.category_id
        LEFT JOIN suppliers s ON p.supplier_id=s.supplier_id
        WHERE 1";
$params = [];

if($search !== ''){
    $sql .= " AND (p.product_name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if($category !== ''){
    $sql .= " AND p.category_id=?";
    $params[] = $category;
}
if($supplier !== ''){
    $sql .= " AND p.supplier_id=?";
    $params[] = $supplier;
}

$sql .= " ORDER BY p.product_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['products'=>$products]);
