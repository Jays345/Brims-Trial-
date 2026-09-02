<?php
header('Content-Type: application/json');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=brims_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT category_name FROM categories ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);

} catch(Exception $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
