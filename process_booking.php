<?php
require_once 'db_connect.php'; // Database connection
session_start();

// Show all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debugging output
    echo "<pre>";
    echo "Raw POST data: ";
    var_dump($_POST);
    echo "</pre>";

    // Check if this is a **status update** action
    if (isset($_POST['booking_id']) && isset($_POST['action'])) {
        // STATUS UPDATE
        $booking_id = intval($_POST['booking_id']);
        $action = $_POST['action'];

        if (!$booking_id || !in_array($action, ['accept', 'decline'])) {
            die("Invalid status update request.");
        }

        // Map action to new status
        $new_status = ($action === 'accept') ? 'confirmed' : 'cancelled';

        // Prepare and execute update statement
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $new_status, $booking_id);

        if ($stmt->execute()) {
            header("Location: bookings.php"); // Redirect to bookings page
            exit;
        } else {
            die("Error updating status: " . $stmt->error);
        }

    } 
    // Else: NEW BOOKING INSERTION
    elseif (
        isset($_POST['listing_id']) && 
        isset($_POST['user_id']) && 
        isset($_POST['price']) &&
        isset($_POST['move_in']) && 
        isset($_POST['move_out']) &&
        isset($_POST['occupants'])
    ) {
        // Validate session (optional but recommended)
        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $_POST['user_id']) {
            die("Unauthorized action.");
        }

        // Sanitize and validate inputs
        $listing_id = filter_var($_POST['listing_id'], FILTER_VALIDATE_INT);
        $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $move_in = $_POST['move_in'];
        $move_out = $_POST['move_out'];
        $occupants = filter_var($_POST['occupants'], FILTER_VALIDATE_INT);

        // Validate input values
        if (!$listing_id || !$user_id || !$price || !$occupants || !strtotime($move_in) || !strtotime($move_out)) {
            die("Invalid input values.");
        }

        if (strtotime($move_in) > strtotime($move_out)) {
            die("Move-in date cannot be after move-out date.");
        }

        // Prepare and execute insert statement
        $stmt = $conn->prepare("INSERT INTO bookings (listing_id, user_id, move_in, move_out, occupants, price) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iissid", $listing_id, $user_id, $move_in, $move_out, $occupants, $price);

        if ($stmt->execute()) {
            header("Location: home.php"); // Redirect to home page
            exit;
        } else {
            die("Error inserting booking: " . $stmt->error);
        }
    } 
    else {
        die("Invalid request: missing required data.");
    }
} else {
    die("Invalid request method.");
}
?>
