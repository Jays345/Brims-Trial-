<?php
header('Content-Type: application/json');
$pdo = new PDO("mysql:host=localhost;dbname=smart_biz;charset=utf8");

$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($categories);
