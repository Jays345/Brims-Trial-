<?php

$servername = "localhost";
$db_name = "smart_biz";         


$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("<script>alert('❌ Database connection failed. Please check your XAMPP MySQL settings.');</script>");
}

// --- HELPER FUNCTION --- //
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// --- PROCESS REGISTRATION --- //
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {

    // Sanitize inputs
    $username = sanitize_input($_POST["username"]);
    $full_name = sanitize_input($_POST["full_name"]);
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $role = sanitize_input($_POST["role"]);
    $account_type = sanitize_input($_POST["account_type"]);
    $dob = !empty($_POST["dob"]) ? $_POST["dob"] : NULL;
    $phone = sanitize_input($_POST["phone"]);
    $country = sanitize_input($_POST["country"]);
    $business_name = sanitize_input($_POST["business_name"]);
    $business_type = sanitize_input($_POST["business_type"]);
    $employees = !empty($_POST["employees"]) ? intval($_POST["employees"]) : NULL;
    $job_title = sanitize_input($_POST["job_title"]);
    $terms_agreed = isset($_POST["terms_agreed"]) ? 1 : 0;
    $newsletter_subscribed = isset($_POST["newsletter_subscribed"]) ? 1 : 0;

    // --- BASIC VALIDATION --- //
    $errors = [];

    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (!$terms_agreed) {
        $errors[] = "You must agree to the terms.";
    }

    // --- STOP IF ERRORS FOUND --- //
    if (!empty($errors)) {
        echo "<script>alert('Error:\\n" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }

    // --- CHECK FOR EXISTING USER --- //
    $check_user = $conn->prepare("SELECT email FROM users WHERE username = ? OR email = ?");
    $check_user->bind_param("ss", $username, $email);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        echo "<script>alert('Username or Email already exists.'); window.history.back();</script>";
        $check_user->close();
        $conn->close();
        exit;
    }
    $check_user->close();

    // --- HASH PASSWORD (BCRYPT) --- //
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // --- INSERT USER --- //
    $stmt = $conn->prepare("INSERT INTO users 
        (username, full_name, email, password, role, account_type, dob, phone, country, business_name, business_type, employees, job_title, terms_agreed, newsletter_subscribed)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "<script>alert('❌ Database query error: " . htmlspecialchars($conn->error) . "');</script>";
        exit;
    }

    $stmt->bind_param(
        "ssssssssssissii",
        $username,
        $full_name,
        $email,
        $hashed_password,
        $role,
        $account_type,
        $dob,
        $phone,
        $country,
        $business_name,
        $business_type,
        $employees,
        $job_title,
        $terms_agreed,
        $newsletter_subscribed
    );

    if ($stmt->execute()) {
        echo "<script>
                alert(' Registration Successful! Redirecting you to the login page...');
                window.location.href = 'login.html';
              </script>";
    } else {
        echo "<script>alert('❌ Registration failed. Please try again later.'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>
