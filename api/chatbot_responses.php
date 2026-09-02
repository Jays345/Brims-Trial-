<?php
header('Content-Type: application/json');
include '../db_connect.php'; 

$q = strtolower(trim($_GET['q'] ?? ''));
$user_id = 1; 

// MAIN RESPONSES 
$responses = [
    // GREETINGS 
    'hello' => 'Hi there ! I’m BRIMS Assistant — ready to help with your business insights!',
    'hi' => 'Hello!  How can I assist you today?',
    'hey' => 'Hey there! What would you like to check in BRIMS?',
    'good morning' => ' Good morning! Let’s make today productive!',
    'good evening' =>  'Good evening! Checking up on your reports or inventory?',

    // SMALL TALK / GENERAL
    'how are you' => 'I am just lines of code but am okay and how are you?',
    'who are you' => ' I’m BRIMS Assistant — your intelligent business companion built to simplify your workflow.',
    'what is brims' => 'BRIMS (Business Resource and Inventory Management System) helps manage products, suppliers, orders, budgets, and reports — all in one place!',
    'what can you do' => ' I can guide you through BRIMS features, explain data trends, and even generate insights from your database.',
    'thanks' => 'You’re very welcome! ',
    'thank you' => 'You’re most welcome! I’m always here to assist.',
    'bye' => ' Goodbye! Have a great and productive day ahead!',
    'good night' => ' Good night! Don’t forget to back up your data before closing!',

    // HELP / SUPPORT
    'help' => 'I’m here to help! Try asking about “reports,” “sales,” “budget,” or “suppliers.”',
    'support' => ' For technical issues, contact system support or check the Help page.',
    'error' => 'If something isn’t working, try refreshing your page or checking your database connection. 🚧',
    'connection' => 'Ensure your backend API (PHP) is running under `localhost/smart_biz/api`.'
];

// PAGE REDIRECTIONS
$redirects = [
    'sales' => ['url' => 'index.html', 'msg' => ' Redirecting you to the Sales section for detailed performance insights...'],
    'report' => ['url' => 'reports.html', 'msg' => ' Opening the Reports page — you can view or download detailed summaries.'],
    'product' => ['url' => 'products.php', 'msg' => ' Redirecting to the Products page.'],
    'order' => ['url' => 'orders.html', 'msg' => ' Let’s open the Orders section to manage current and pending orders.'],
    'supplier' => ['url' => 'suppliers.html', 'msg' => ' Redirecting to the Suppliers page for vendor details and contacts.'],
    'dashboard' => ['url' => 'dashboard.html', 'msg' => ' Taking you to your Dashboard overview.'],
    'settings' => ['url' => 'settings.html', 'msg' => ' Opening the Settings page for customization options.']
];

//DETECT REDIRECTION INTENT 
$redirect_url = null;
$redirect_message = null;
$intent_keywords = ['go to', 'open', 'show', 'take me to', 'navigate to', 'redirect to'];

foreach ($redirects as $keyword => $data) {
    if (strpos($q, $keyword) !== false) {
        foreach ($intent_keywords as $intent) {
            if (strpos($q, $intent) !== false || $q === $keyword) {
                $redirect_url = $data['url'];
                $redirect_message = $data['msg'];
                break 2;
            }
        }
    }
}

// === ANALYTICAL QUERIES HANDLING ===
$chartData = null;
$chartType = null;
$finalReply = null;

if (preg_match('/product|products/', $q)) {
    if (preg_match('/month|monthly/', $q)) {
        $finalReply = getProductSummary($conn, 'month');
    } elseif (preg_match('/year|yearly/', $q)) {
        $finalReply = getProductSummary($conn, 'year');
    } elseif (preg_match('/quarter|quarterly/', $q)) {
        $finalReply = getProductSummary($conn, 'quarter');
    }
} elseif (preg_match('/order|orders/', $q)) {
    if (preg_match('/month|monthly/', $q)) {
        $finalReply = getOrderSummary($conn, 'month');
    } elseif (preg_match('/year|yearly/', $q)) {
        $finalReply = getOrderSummary($conn, 'year');
    } elseif (preg_match('/quarter|quarterly/', $q)) {
        $finalReply = getOrderSummary($conn, 'quarter');
    }
} elseif (preg_match('/supplier|suppliers/', $q)) {
    if (preg_match('/month|monthly/', $q)) {
        $finalReply = getSupplierSummary($conn, 'month');
    } elseif (preg_match('/year|yearly/', $q)) {
        $finalReply = getSupplierSummary($conn, 'year');
    } elseif (preg_match('/quarter|quarterly/', $q)) {
        $finalReply = getSupplierSummary($conn, 'quarter');
    }
}

// === FUZZY MATCHING FALLBACK ===
if (!$finalReply && !$redirect_message) {
    $bestMatch = null;
    $highestSimilarity = 0;

    foreach ($responses as $keyword => $reply) {
        similar_text($q, $keyword, $similarity);
        if ($similarity > $highestSimilarity && $similarity > 40) {
            $highestSimilarity = $similarity;
            $bestMatch = $reply;
        }
    }

    $finalReply = $bestMatch ?: " Hmm, I’m not entirely sure about that yet — but I’m learning! Try asking about sales, reports, stock, or suppliers.";
}

// === FINAL REPLY ===
$finalReply = $redirect_message ?: $finalReply;

// === LOG CHAT ===
$stmt = $conn->prepare("INSERT INTO chatbot_logs (user_id, user_message, bot_reply) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $q, $finalReply);
$stmt->execute();

// === RETURN RESPONSE ===
echo json_encode([
    'reply' => $finalReply,
    'confidence' => 99.5,
    'redirect' => $redirect_url,
    'chartData' => $chartData ?? null,
    'chartType' => $chartType ?? null
]);

// === HELPER FUNCTIONS ===

function getDateCondition($timeframe, $column) {
    switch ($timeframe) {
        case 'month': return "WHERE MONTH($column) = MONTH(CURDATE()) AND YEAR($column) = YEAR(CURDATE())";
        case 'year': return "WHERE YEAR($column) = YEAR(CURDATE())";
        case 'quarter': return "WHERE QUARTER($column) = QUARTER(CURDATE()) AND YEAR($column) = YEAR(CURDATE())";
        default: return '';
    }
}

function getOrderSummary($conn, $timeframe) {
    global $chartData, $chartType;
    $dateCond = getDateCondition($timeframe, 'order_date');
    $sql = "SELECT DATE(order_date) AS date, SUM(total_amount) AS total FROM orders $dateCond GROUP BY DATE(order_date)";
    $res = $conn->query($sql);

    $labels = []; $values = [];
    while ($r = $res->fetch_assoc()) {
        $labels[] = $r['date'];
        $values[] = $r['total'];
    }

    $chartData = ['labels' => $labels, 'values' => $values];
    $chartType = 'bar';

    $sum = array_sum($values);
    return " For this $timeframe, you made " . count($labels) . " orders totaling approximately ₹" . number_format($sum, 2) . ".";
}

function getProductSummary($conn, $timeframe) {
    global $chartData, $chartType;
    $dateCond = getDateCondition($timeframe, 'created_at');
    $sql = "SELECT category, COUNT(*) AS total FROM products $dateCond GROUP BY category";
    $res = $conn->query($sql);

    $labels = []; $values = [];
    while ($r = $res->fetch_assoc()) {
        $labels[] = $r['category'];
        $values[] = $r['total'];
    }

    $chartData = ['labels' => $labels, 'values' => $values];
    $chartType = 'pie';

    return "🛒 This $timeframe, there are **" . array_sum($values) . " new products** distributed across " . count($labels) . " categories.";
}

function getSupplierSummary($conn, $timeframe) {
    global $chartData, $chartType;
    $dateCond = getDateCondition($timeframe, 'created_at');
    $sql = "SELECT region, COUNT(*) AS total FROM suppliers $dateCond GROUP BY region";
    $res = $conn->query($sql);

    $labels = []; $values = [];
    while ($r = $res->fetch_assoc()) {
        $labels[] = $r['region'];
        $values[] = $r['total'];
    }

    $chartData = ['labels' => $labels, 'values' => $values];
    $chartType = 'bar';

    return " In this $timeframe, there were **" . array_sum($values) . " active suppliers** across " . count($labels) . " regions.";
}
?>
