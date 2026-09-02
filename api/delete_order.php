<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost","smart_biz"); // 
$id = $_GET['id'];
$conn->query("DELETE FROM orders WHERE order_id=$id");
echo json_encode(["success"=>true]);
$conn->close();
?>
