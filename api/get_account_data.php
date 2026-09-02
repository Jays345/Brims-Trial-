<?php
header("Content-Type: application/json");

$servername = "localhost";  
$dbname = "smart_biz";    

$conn = new mysqli($servername, $dbname);

if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "DB connection failed"]);
  exit;
}

// Example: fetch by user_id (you can also use email if logged in)
$user_id = $_GET['user_id'] ?? 'cmhhyeerm0001la04hlsyj6y';

$query = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$query->bind_param("s", $user_id);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode(["status" => "success", "data" => $row]);
} else {
  echo json_encode(["status" => "error", "message" => "User not found"]);
}

$conn->close();
?>
