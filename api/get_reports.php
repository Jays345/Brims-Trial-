<?php
header("Content-Type: application/json");
require_once "db_connect.php";

$data = [];
$sql = "SELECT id, report_title, total_sales, total_orders, total_products, total_suppliers,total_expenses, generated_at
        FROM reports
        ORDER BY generated_at DESC";

$res = $conn->query($sql);
if(!$res){
    echo json_encode(["error" => $conn->error]);
    exit;
}

while($row = $res->fetch_assoc()){
    // Profit placeholder (if you don't have total_expenses column)
    $row['profit'] = isset($row['total_sales']) ? floatval($row['total_sales']) : 0;
    
    // Top seller placeholder
    $row['top_seller'] = "N/A";

    $data[] = $row;
}

echo json_encode($data, JSON_PRETTY_PRINT);
?>
