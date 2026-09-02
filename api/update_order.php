<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost","smart_biz"); 
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$qty = $data['quantity'];
$total = $data['total'];
$status = $data['status'];
$conn->query("UPDATE orders SET quantity=$qty, total_price=$total, status='$status' WHERE order_id=$id");
echo json_encode(["success"=>true]);
$conn->close();
?>
