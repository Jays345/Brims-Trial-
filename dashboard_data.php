<?php
header('Content-Type: application/json');


$host = 'localhost';
$db   = 'smart_biz';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}


// Stats

$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalSuppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn();

// Today's date for dynamic calculation
$today = date('Y-m-d');

// If there are no sales/expenses today, returns 0
$dailySales = $pdo->query("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = '$today'")->fetchColumn();
$dailyExpenses = $pdo->query("SELECT SUM(amount) FROM expenses WHERE DATE(expense_date) = '$today'")->fetchColumn();

$dailySales = $dailySales ?: 0;
$dailyExpenses = $dailyExpenses ?: 0;


// Low Stock Alerts

$lowStockStmt = $pdo->query("
    SELECT p.product_name AS name, p.product_id AS sku, p.stock_quantity AS stock, c.category_name AS category
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.stock_quantity <= 10
    ORDER BY p.stock_quantity ASC
    LIMIT 20
");
$lowStock = $lowStockStmt->fetchAll();

// Monthly Sales, Expenses, Profit

$monthlySales = $monthlyExpenses = $monthlyProfit = array_fill(0, 12, 0);

// Sales per month
$salesData = $pdo->query("
    SELECT MONTH(sale_date) AS month, SUM(total_amount) AS total
    FROM sales
    WHERE YEAR(sale_date) = YEAR(CURDATE())
    GROUP BY month
")->fetchAll();
foreach ($salesData as $row) {
    $monthlySales[$row['month'] - 1] = (float)$row['total'];
}

// Expenses per month
$expenseData = $pdo->query("
    SELECT MONTH(expense_date) AS month, SUM(amount) AS total
    FROM expenses
    WHERE YEAR(expense_date) = YEAR(CURDATE())
    GROUP BY month
")->fetchAll();
foreach ($expenseData as $row) {
    $monthlyExpenses[$row['month'] - 1] = (float)$row['total'];
}

// Profit = Sales - Expenses
for ($i = 0; $i < 12; $i++) {
    $monthlyProfit[$i] = $monthlySales[$i] - $monthlyExpenses[$i];
}

// Return JSON

echo json_encode([
    'stats' => [
        'totalProducts' => (int)$totalProducts,
        'totalSuppliers' => (int)$totalSuppliers,
        'totalOrders' => (int)$totalOrders,
        'totalRevenue' => (float)$totalRevenue ?: 0,
        'dailySales' => (float)$dailySales,
        'dailyExpenses' => (float)$dailyExpenses,
    ],
    'lowStock' => $lowStock,
    'monthlySales' => $monthlySales,
    'monthlyExpenses' => $monthlyExpenses,
    'monthlyProfit' => $monthlyProfit
]);
?>
