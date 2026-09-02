<?php
header('Content-Type: application/json');
include '../db_connect.php';

$result = $conn->query("SELECT * FROM chatbot_logs ORDER BY created_at DESC LIMIT 100");
$logs = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($logs);
?>
