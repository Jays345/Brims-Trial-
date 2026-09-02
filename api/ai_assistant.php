<?php
header('Content-Type: application/json');
include '../db_connect.php';

$q = strtolower(trim($_GET['q'] ?? ''));
$user_id = 1; // Replace with session user ID if available

$response = "Hmm  I’m not sure about that yet. Try asking about *sales*, *profit*, or *low stock.*";

// --------------- SIMPLE INTENTS -----------------
if (strpos($q, 'hello') !== false || strpos($q, 'hi') !== false) {
    $response = " Hello! I’m your BRIMS AI Assistant. How can I help with your business insights today?";
}
elseif (strpos($q, 'sales') !== false) {
    $sql = "SELECT SUM(amount) AS total_sales, DATE(order_date) AS date 
            FROM orders GROUP BY DATE(order_date) ORDER BY date DESC LIMIT 7";
    $result = $conn->query($sql);

    $data = [];
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
        $total += $row['total_sales'];
    }

    $avg = $total / max(count($data), 1);
    $response = "Here’s your recent sales trend:\n";
    foreach ($data as $r) {
        $response .= "- " . $r['date'] . ": $" . number_format($r['total_sales'], 2) . "\n";
    }
    $response .= "\nAverage daily sales: $" . number_format($avg, 2);
}
elseif (strpos($q, 'profit') !== false) {
    $sql = "SELECT SUM(sales) - SUM(expenses) AS profit FROM finance_summary WHERE MONTH(date) = MONTH(CURDATE())";
    $res = $conn->query($sql)->fetch_assoc();
    $profit = $res['profit'] ?? 0;

    if ($profit > 0) {
        $response = " This month’s profit so far is $" . number_format($profit, 2) . ". Keep it up!";
    } else {
        $response = " You currently have a negative profit of $" . number_format(abs($profit), 2) . ". Consider reviewing expenses.";
    }
}
elseif (strpos($q, 'low stock') !== false || strpos($q, 'inventory') !== false) {
    $sql = "SELECT name, stock FROM products WHERE stock <= 5 LIMIT 5";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $response = " The following products are low in stock:\n";
        while ($r = $result->fetch_assoc()) {
            $response .= "- {$r['name']} ({$r['stock']} units left)\n";
        }
    } else {
        $response = " All products are sufficiently stocked right now.";
    }
}
elseif (strpos($q, 'supplier') !== false) {
    $sql = "SELECT COUNT(*) AS total FROM suppliers";
    $total = $conn->query($sql)->fetch_assoc()['total'];
    $response = " You currently have {$total} active suppliers.";
}
elseif (strpos($q, 'insight') !== false || strpos($q, 'summary') !== false) {
    $sql_sales = "SELECT SUM(amount) AS sales FROM orders WHERE MONTH(order_date) = MONTH(CURDATE())";
    $sql_exp = "SELECT SUM(amount) AS expenses FROM expenses WHERE MONTH(date) = MONTH(CURDATE())";
    $sales = $conn->query($sql_sales)->fetch_assoc()['sales'] ?? 0;
    $expenses = $conn->query($sql_exp)->fetch_assoc()['expenses'] ?? 0;
    $profit = $sales - $expenses;

    $response = " This month’s summary:\n";
    $response .= "- Sales: $" . number_format($sales, 2) . "\n";
    $response .= "- Expenses: $" . number_format($expenses, 2) . "\n";
    $response .= "- Profit: $" . number_format($profit, 2) . "\n";

    if ($profit > 0) $response .= " Suggestion: You’re doing well! Consider reinvesting 10–15% of profits into marketing.";
    else $response .= " Suggestion: Expenses are high — review supplier costs and inventory wastage.";
}
elseif (strpos($q, 'bye') !== false || strpos($q, 'thank') !== false) {
    $response = " You’re welcome! Keep up the good work — BRIMS is here whenever you need insights!";
}

// --- LOG THE CONVERSATION ---
$stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, user_message, bot_reply) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $q, $response);
$stmt->execute();

echo json_encode(['reply' => nl2br($response)]);
?>
