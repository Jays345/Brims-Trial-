<?php
// Database connection
$servername = "localhost";
$dbname = "smart_biz";       // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Get data from POST request
$data = json_decode(file_get_contents("php://input"), true);

$full_name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$business_name = $data['businessName'] ?? '';
$business_website = $data['businessWebsite'] ?? '';
$user_id = "cmhhyeerm0001la04hlsyj6y"; // You can change this dynamically if users log in

if (!$full_name || !$email) {
  echo json_encode(["status" => "error", "message" => "Name and Email are required"]);
  exit;
}

// Check if user already exists
$check = $conn->prepare("SELECT id FROM user_settings WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  // Update existing record
  $update = $conn->prepare("UPDATE user_settings SET full_name=?, business_name=?, business_website=? WHERE email=?");
  $update->bind_param("ssss", $full_name, $business_name, $business_website, $email);
  $update->execute();
  $update->close();

  echo json_encode(["status" => "success", "message" => "Profile updated successfully"]);
} else {
  // Insert new record
  $insert = $conn->prepare("INSERT INTO user_settings (full_name, email, business_name, business_website, user_id) VALUES (?, ?, ?, ?, ?)");
  $insert->bind_param("sssss", $full_name, $email, $business_name, $business_website, $user_id);
  $insert->execute();
  $insert->close();

  echo json_encode(["status" => "success", "message" => "Profile saved successfully"]);
}

$conn->close();
?>
