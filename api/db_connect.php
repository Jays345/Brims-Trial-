<?php
$servername = "localhost";
$dbname = "smart_biz"; 

$conn = new mysqli($servername, $dbname);

if ($conn->connect_error) {
  die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}
?>
