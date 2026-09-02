<?php
// Allow cross-origin if needed (optional for localhost testing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");


$host = "localhost";
$dbname = "smart_biz"; 

$conn = new mysqli($host, $user, $pass, $dbname);


if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch all contact requests
$sql = "SELECT id, name, email, phone, message, submitted_at 
        FROM contact_requests 
        ORDER BY submitted_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit();
}

$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "email" => $row["email"],
        "phone" => $row["phone"],
        "message" => $row["message"],
        "submitted_at" => $row["submitted_at"]
    ];
}

echo json_encode($requests);

$conn->close();
?>
