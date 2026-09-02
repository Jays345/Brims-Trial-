<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, c.category_name, s.supplier_name
                       FROM products p
                       LEFT JOIN categories c ON p.category_id=c.category_id
                       LEFT JOIN suppliers s ON p.supplier_id=s.supplier_id
                       WHERE p.product_id=?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($product);
