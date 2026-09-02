<?php 
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");


$conn = new mysqli("localhost", "root", "", "smart_biz");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data['rows']) || empty($data['rows'])) {
    echo json_encode(["status" => "error", "message" => "No data received."]);
    exit;
}

// Prepare SQL statement
$stmt = $conn->prepare("
  INSERT INTO budgets (category, approved_budget, proposed_budget, difference_amount, difference_percent)
  VALUES (?, ?, ?, ?, ?)
");

$successCount = 0;
foreach ($data['rows'] as $row) {
    $category = $row['category'] ?? '';
    $approved = $row['approved'] ?? 0;
    $proposed = $row['proposed'] ?? 0;
    $diff = $row['diff'] ?? 0;
    $diffPercent = $row['diffPercent'] ?? 0;

    if ($category !== '') {
        $stmt->bind_param("sdddd", $category, $approved, $proposed, $diff, $diffPercent);
        if ($stmt->execute()) {
            $successCount++;
        } else {
            file_put_contents("sql_errors.txt", $stmt->error . PHP_EOL, FILE_APPEND);
        }
    }
}

if ($successCount > 0) {
    echo json_encode(["status" => "success", "message" => " Budget saved successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => " No rows inserted. Check SQL error log."]);
}

$stmt->close();
$conn->close();
?>
