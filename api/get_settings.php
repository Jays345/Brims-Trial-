<?php
header('Content-Type: application/json');
include '../db_connect.php';
session_start();


$user_id = $_SESSION['user_id'] ?? 1;

// Fetch user info
$user_sql = "SELECT full_name, email, business_name, business_type, country, account_type 
              FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

//  Fetch settings info
$settings_sql = "SELECT theme, sidebar_collapsed, accent_color, email_notifications, sms_notifications 
                 FROM settings WHERE user_id = ?";
$settings_stmt = $conn->prepare($settings_sql);
$settings_stmt->bind_param("i", $user_id);
$settings_stmt->execute();
$settings_result = $settings_stmt->get_result();
$settings_data = $settings_result->fetch_assoc();


if ($user_data && $settings_data) {
    echo json_encode([
        "success" => true,
        "theme" => $settings_data['theme'],
        "sidebarCollapsed" => $settings_data['sidebar_collapsed'],
        "accentColor" => $settings_data['accent_color'],
        "emailNotifications" => $settings_data['email_notifications'],
        "smsNotifications" => $settings_data['sms_notifications'],
        "adminUsername" => $user_data['full_name'],
        "adminEmail" => $user_data['email'],
        "businessName" => $user_data['business_name'],
        "businessType" => $user_data['business_type'],
        "country" => $user_data['country'],
        "accountType" => $user_data['account_type']
    ]);
} else {
    echo json_encode(["success" => false, "msg" => "User or settings not found"]);
}

$user_stmt->close();
$settings_stmt->close();
$conn->close();
?>
