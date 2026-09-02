<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "smart_biz";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch all suppliers
$sql = "SELECT supplier_id, supplier_name, contact_person, email, phone, address, status, created_at 
        FROM suppliers 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

$suppliers = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

// Total suppliers
$total_suppliers = count($suppliers);

// Count active suppliers
$active_result = $conn->query("SELECT COUNT(*) AS active_count FROM suppliers WHERE status='active'");
$active_suppliers = ($active_result && $active_result->num_rows > 0)
    ? (int)$active_result->fetch_assoc()['active_count']
    : 0;

// Count pending suppliers
$pending_result = $conn->query("SELECT COUNT(*) AS pending_count FROM suppliers WHERE status='pending'");
$pending_suppliers = ($pending_result && $pending_result->num_rows > 0)
    ? (int)$pending_result->fetch_assoc()['pending_count']
    : 0;

// Top supplier (most recently added active supplier)
$top_result = $conn->query("SELECT supplier_name FROM suppliers WHERE status='active' ORDER BY created_at DESC LIMIT 1");
$top_supplier = ($top_result && $top_result->num_rows > 0)
    ? $top_result->fetch_assoc()['supplier_name']
    : "N/A";

// Send JSON response
echo json_encode([
    "suppliers" => $suppliers,
    "stats" => [
        "total" => $total_suppliers,
        "active" => $active_suppliers,
        "pending" => $pending_suppliers,
        "top_supplier" => $top_supplier
    ]
], JSON_PRETTY_PRINT);

$conn->close();
?>
