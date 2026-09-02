<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8", "root", "");

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("DELETE FROM products WHERE product_id=?");
$stmt->execute([$id]);

echo json_encode(['success'=>true]);
