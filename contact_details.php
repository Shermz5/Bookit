<?php
// Assume $conn is your mysqli connection and $user_id is the logged-in user's id
session_start();
require 'db_connect.php'; // PDO connection

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize POST inputs (you might want to do more validation here)
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zipCode'] ?? '';

    // Check if the profile contact already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM profile_contact_details WHERE user_id = ?");
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE profile_contact_details SET email=?, phone=?, address=?, country=?, city=?, state=?, zip=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $email, $phone, $address, $country, $city, $state, $zip, $user_id);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO profile_contact_details (email, phone, address, country, city, state, zip, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssi", $email, $phone, $address, $country, $city, $state, $zip, $user_id);
    }

   if ($stmt->execute()) {
    // Redirect to the profile page with a query parameter indicating success
    header('Location: profile.php');
    exit;
} else {
    $error_msg = "Error updating contact details: " . $stmt->error;
}
    $stmt->close();

    
}



// After handling POST (or if GET), retrieve current contact details to pre-fill the form
$stmt = $conn->prepare("SELECT email, phone, address, country, city, state, zip FROM profile_contact_details WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$contact_details = $result->fetch_assoc() ?? [];
$stmt->close();

// Now you can use $contact_details['email'], $contact_details['phone'], etc. in your form inputs
?>
