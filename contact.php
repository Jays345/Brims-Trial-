<?php

$host = "localhost";         
$dbname = "smart_biz";   
$username = "root";     
$password= "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data and sanitize
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Simple validation
    if ($name && $email && $phone && $message) {
    
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (:name, :email, :phone, :message)");

        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':message', $message);

        if ($stmt->execute()) {
            echo "<script>alert('Message sent successfully!'); window.location.href='contact.html';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to send message. Please try again.'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit;
    }
} else {
    // Redirect if accessed directly
    header("Location: contact.html");
    exit;
}
?>
