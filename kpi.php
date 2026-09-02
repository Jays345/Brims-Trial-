<?php
header("Content-Type: application/json");
require_once "db_connect.php";

// ---------- KPIs ----------
$kpi = [
    "totalSales" => 0,
    "totalOrders" => 0,
    "totalExpenses" => 0,
    "totalProducts" => 0,
    "activeSuppliers" => 0
];

// Queries
$kpiQueries = [
    "totalSales" => "SELECT SUM(total_amount) AS val FROM orders",
    "totalOrders" => "SELECT COUNT(order_id) AS val FROM orders",
    "totalExpenses" => "SELECT SUM(expense_amount) AS val FROM orders",
    "totalProducts" => "SELECT COUNT(product_id) AS val FROM products",
    "activeSuppliers" => "SELECT COUNT(supplier_id) AS val FROM suppliers WHERE status='active'"
];

foreach($kpiQueries as $key => $sql){
    $res = $conn->query($sql);
    if($res){
        $row = $res->fetch_assoc();
        $kpi[$key] = floatval($row['val'] ?? 0);
    }
}

// ---------- Monthly Sales & Expenses ----------
$monthly = ["labels"=>[], "sales"=>[], "expenses"=>[]];
$sql = "SELECT DATE_FORMAT(order_date,'%b %Y') AS month, SUM(total_amount) AS sales, SUM(expense_amount) AS expenses
        FROM orders
        GROUP BY YEAR(order_date), MONTH(order_date)
        ORDER BY order_date ASC";
$res = $conn->query($sql);
if($res){
    while($row = $res->fetch_assoc()){
        $monthly["labels"][] = $row["month"];
        $monthly["sales"][] = floatval($row["sales"]);
        $monthly["expenses"][] = floatval($row["expenses"]);
    }
}

// ---------- Category Breakdown ----------
$categories = [];
$sql = "SELECT category, SUM(price*quantity) AS total FROM products GROUP BY category";
$res = $conn->query($sql);
if($res){
    while($row = $res->fetch_assoc()){
        $categories[$row["category"]] = floatval($row["total"]);
    }
}

// ---------- Staff Performance ----------
$staff = [];
$sql = "SELECT staff_name, SUM(total_amount) AS total FROM orders GROUP BY staff_name ORDER BY total DESC";
$res = $conn->query($sql);
if($res){
    while($row = $res->fetch_assoc()){
        $staff[$row["staff_name"]] = floatval($row["total"]);
    }
}

// Output JSON
echo json_encode([
    "kpis" => $kpi,
    "monthly" => $monthly,
    "categories" => $categories,
    "staff" => $staff
]);
?>
