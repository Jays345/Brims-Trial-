<?php
header("Content-Type: application/json");
require_once "db_connect.php";

// Calculate totals from orders table
$sqlTotals = "
    SELECT 
        SUM(total_price) AS total_sales,
        COUNT(*) AS total_orders,
        SUM(total_expenses) AS total_expenses
    FROM orders
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
";
$resTotals = $conn->query($sqlTotals);
$totals = $resTotals->fetch_assoc();

// --- Generate categories totals dynamically ---
$sqlCategories = "
    SELECT c.name AS category, SUM(o.total_price) AS total
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE MONTH(o.created_at) = MONTH(CURRENT_DATE())
    GROUP BY c.id
";
$catRes = $conn->query($sqlCategories);
$categories = [];
while($row = $catRes->fetch_assoc()){
    $categories[$row['category']] = floatval($row['total']);
}

// Insert into reports table
$reportTitle = date("F Y");
$insertSql = "
    INSERT INTO reports 
    (report_title, total_sales, total_orders, total_expenses, categories, generated_at)
    VALUES (?, ?, ?, ?, ?, NOW())
";
$stmt = $conn->prepare($insertSql);
$stmt->bind_param(
    "sddds",
    $reportTitle,
    $totals['total_sales'],
    $totals['total_orders'],
    $totals['total_expenses'],
    json_encode($categories)
);
if($stmt->execute()){
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["error"=>$stmt->error]);
}
?>
