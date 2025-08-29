<?php
session_start();

$host = 'localhost';
$db   = 'accommodation_rental';
$user = 'root';
$pass = '';

// Connect MySQLi
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Fetch user info
$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password_hash'])) {
        // Set user session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'images/default-avatar.jpg';

        // Check if user has listings (i.e., is a host)
        $stmtHost = $conn->prepare("SELECT COUNT(*) AS listing_count FROM listings WHERE host_id = ?");
        $stmtHost->bind_param("i", $user['id']);
        $stmtHost->execute();
        $resultHost = $stmtHost->get_result();
        $rowHost = $resultHost->fetch_assoc();

        if ($rowHost['listing_count'] > 0) {
            $_SESSION['host_id'] = $user['id'];  // User is host
        }

        $stmtHost->close();
        $stmt->close();
        $conn->close();

        header("Location: home.php");
        exit();
    } else {
        header("Location: login.html?error=invalid");
        exit();
    }
} else {
    header("Location: login.html?error=invalid");
    exit();
}
