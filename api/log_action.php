<?php
function logAction($conn, $user_id, $username, $action_type, $description) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (user_id, username, action_type, description, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $user_id, $username, $action_type, $description, $ip);
    $stmt->execute();
}
?>
