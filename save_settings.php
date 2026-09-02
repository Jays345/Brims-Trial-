<?php
header('Content-Type: application/json');
include 'db.php'; // your DB connection

$data = json_decode(file_get_contents('php://input'), true);

if(!$data) {
    echo json_encode(['error'=>'No data received']);
    exit;
}

// Sanitize inputs
$site_name = $data['site_name'] ?? '';
$time_zone = $data['time_zone'] ?? 'UTC';
$theme = $data['theme'] ?? 'light';
$username = $data['username'] ?? 'admin';
$password = $data['password'] ?? '';

// Hash password if provided
$password_sql = '';
if(!empty($password)){
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $password_sql = ", password = :password";
}

try{
    // Check if settings row exists
    $stmt = $conn->prepare("SELECT id FROM settings WHERE id = 1");
    $stmt->execute();
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if($exists){
        $sql = "UPDATE settings SET site_name=:site_name, time_zone=:time_zone, theme=:theme, username=:username $password_sql WHERE id=1";
    } else {
        $sql = "INSERT INTO settings (id, site_name, time_zone, theme, username".(!empty($password)?",password":"").") VALUES (1, :site_name, :time_zone, :theme, :username".(!empty($password)?",:password":"").")";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':site_name', $site_name);
    $stmt->bindParam(':time_zone', $time_zone);
    $stmt->bindParam(':theme', $theme);
    $stmt->bindParam(':username', $username);
    if(!empty($password)){
        $stmt->bindParam(':password', $hashed);
    }
    $stmt->execute();

    echo json_encode(['success'=>true]);
}catch(Exception $e){
    echo json_encode(['error'=>$e->getMessage()]);
}
?>
