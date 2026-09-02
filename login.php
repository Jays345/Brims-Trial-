<?php
session_start();


$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "smart_biz";

$conn = new mysqli($servername, $db_username, $db_password, $db_name);
if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
    $username = sanitize_input($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        echo "<script>alert('Please fill in both fields.'); window.history.back();</script>";
        exit;
    }

    
    $stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                echo "<script>
                        alert('Welcome, Admin!');
                        window.location.href = 'index.html';
                      </script>";
            } else {
                echo "<script>
                        alert('Login Successful!');
                        window.location.href = 'dashboard.html';
                      </script>";
            }
        } else {
            echo "<script>alert('Incorrect password. Please try again.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('No account found with that username.'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>
