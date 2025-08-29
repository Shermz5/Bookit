<?php
require_once 'db_connect.php';

var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($booking_id && ($action === 'accept' || $action === 'decline')) {
        $new_status = $action === 'accept' ? 'confirmed' : 'cancelled';
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        if ($stmt->execute()) {
            echo "Status updated successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Invalid input.";
    }
} else {
    echo "Not a POST request.";
}
