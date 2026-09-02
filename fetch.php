<?php
include 'db.php';

$data = [];

// Live stats
$data['totalProducts'] = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$data['totalSuppliers'] = $conn->query("SELECT COUNT(*) AS count FROM suppliers")->fetch_assoc()['count'];
$data['totalOrders'] = $conn->query("SELECT COUNT(*) AS count FROM orders WHERE status='open'")->fetch_assoc()['count'];
$data['totalRevenue'] = $conn->query("SELECT SUM(total_amount) AS total FROM sales")->fetch_assoc()['total'] ?? 0;

header('Content-Type: application/json');
echo json_encode($data);
?>
