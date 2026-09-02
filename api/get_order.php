<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost","smart_biz"); 
if($conn->connect_error){ echo json_encode(["success"=>false,"message"=>"DB connection failed"]); exit; }

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$query = "SELECT * FROM orders WHERE 1=1";
if($search) $query.=" AND (customer_name LIKE '%$search%' OR product_name LIKE '%$search%')";
if($status && $status!=='all') $query.=" AND status='$status'";
if($from) $query.=" AND order_date >= '$from'";
if($to) $query.=" AND order_date <= '$to'";

$res = $conn->query($query);
$orders = [];
while($row = $res->fetch_assoc()){
    $orders[] = [
        "id"=>$row['order_id'],
        "customer"=>$row['customer_name'],
        "product"=>$row['product_name'],
        "qty"=>$row['quantity'],
        "total"=>$row['total_price'],
        "status"=>$row['status'],
        "date"=>$row['order_date']
    ];
}
echo json_encode(["success"=>true,"orders"=>$orders]);
$conn->close();
?>
