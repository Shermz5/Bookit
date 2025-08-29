<?php
// signup.php
require 'db_connect.php'; // include the DB connection

// Receive form data safely (make sure your form method="POST")
$first_name = $_POST['firstname'] ?? '';
$last_name = $_POST['lastname'] ?? '';
$email = $_POST['email'] ?? '';
$country_code = $_POST['country_code'] ?? '';
$phone = $_POST['phone'] ?? '';
$username = $_POST['username'] ?? '';
$password_raw = $_POST['password'] ?? '';
$agreed_to_terms = isset($_POST['terms']) ? 1 : 0;

// Basic validation (you can improve this)
if (!$first_name || !$last_name || !$email || !$username || !$password_raw) {
    exit('Please fill in all required fields.');
}

// Hash the password
$password = password_hash($password_raw, PASSWORD_BCRYPT);

// Check for duplicate email
$email_check_sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
$stmt = $conn->prepare($email_check_sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$count = $row['count'];

if ($count > 0) {
    echo "Email already exists.";
} else {
    echo "Email is available.";
}

// Insert user
$insert_sql = "INSERT INTO users (first_name, last_name, email, country_code, phone, username, password_hash, agreed_to_terms)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("sssssssi", $first_name, $last_name, $email, $country_code, $phone, $username, $password, $agreed_to_terms);

if ($stmt->execute()) {
    header("Location: login.html");
    exit();
} else {
    exit('Error: Could not create user.');
}
?>
