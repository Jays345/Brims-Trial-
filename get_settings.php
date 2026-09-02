<?php
header('Content-Type: application/json');
include 'db.php'; 

try {
    $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$settings) {
        // Default values if row doesn't exist
        $settings = [
            'site_name' => 'BRIMS',
            'time_zone' => 'UTC',
            'theme' => 'light',
            'username' => 'admin'
        ];
    }
    echo json_encode($settings);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
