<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $listing_id = $_POST['listing_id'];
    $owner_id = $_POST['owner_id'];
    $payment_method = $_POST['payment_method']; // 'Mastercard' or 'EcoCash'

    // Get payment_method_id from name
    $stmt = $conn->prepare("SELECT id FROM payment_methods WHERE method_name = ?");
    $stmt->bind_param("s", $payment_method);
    $stmt->execute();
    $stmt->bind_result($payment_method_id);
    $stmt->fetch();
    $stmt->close();

    // Insert payment
    $stmt = $conn->prepare("INSERT INTO payment_records (user_id, owner_id, listing_id, payment_method_id, amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $user_id, $owner_id, $listing_id, $payment_method_id, $amount);

    if ($stmt->execute()) {
        echo "<script>alert('Payment recorded successfully'); window.location.href = 'confirmation.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
