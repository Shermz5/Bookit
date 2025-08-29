<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$mastercard = $_POST['mastercardNumber'] ?? '';
$ecocash = $_POST['ecocashNumber'] ?? '';

// Check if record exists for this user
$checkSql = "SELECT id FROM payment_info WHERE user_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $user_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // Record exists — update it
    $updateSql = "UPDATE payment_info SET mastercard_number = ?, ecocash_number = ? WHERE user_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssi", $mastercard, $ecocash, $user_id);
    
    if ($updateStmt->execute()) {
        echo "<script>alert('Payment info updated'); window.location.href='add_payment.php';</script>";
    } else {
        echo "Error updating record: " . $updateStmt->error;
    }

    $updateStmt->close();
} else {
    // No record — insert a new one
    $insertSql = "INSERT INTO payment_info (mastercard_number, ecocash_number, user_id) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ssi", $mastercard, $ecocash, $user_id);

    if ($insertStmt->execute()) {
        echo "<script>alert('Payment info saved'); window.location.href='add_payment.php';</script>";
    } else {
        echo "Error inserting record: " . $insertStmt->error;
    }

    $insertStmt->close();
}

$checkStmt->close();
$conn->close();
?>
