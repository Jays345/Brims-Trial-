<?php
header('Content-Type: application/json');

$servername = "localhost";
$database   = "smart_biz"; 
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Fetch all suppliers
$sql = "SELECT * FROM suppliers ORDER BY created_at DESC";
$result = $conn->query($sql);

$suppliers = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $suppliers[] = $row;
  }
}

// Compute simple stats (you can refine logic later)
$total_suppliers = count($suppliers);
$active_suppliers = rand(1, $total_suppliers); // placeholder value
$pending_contracts = max(0, $total_suppliers - $active_suppliers);
$top_supplier = $total_suppliers > 0 ? $suppliers[0]['supplier_name'] : null;

// Prepare response
$response = [
  "suppliers" => $suppliers,
  "totalSuppliers" => $total_suppliers,
  "activeSuppliers" => $active_suppliers,
  "pendingSuppliers" => $pending_contracts,
  "topSupplier" => $top_supplier,
  "topChart" => [] // placeholder for chart data (add later if you track orders)
];

// Send JSON
echo json_encode($response, JSON_PRETTY_PRINT);

// Close connection
$conn->close();
?>
