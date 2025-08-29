<?php
require 'db_connect.php';
session_start();  // Make sure you start the session

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check that the host is logged in
    if (!isset($_SESSION['user_id'])) {
        // 🚀 Redirect to login.html if not logged in
        header("Location: login.html");
        exit;
    }

    $host_id = $_SESSION['user_id'];  // Retrieve the logged-in user's id

    // Basic fields
    $title = $_POST['title'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];
    $description = $_POST['description'];

    // Amenities and rental_conditions (as comma-separated strings)
    $amenities = (isset($_POST['amenities']) && is_array($_POST['amenities'])) ? implode(',', $_POST['amenities']) : '';
    $rental_conditions = (isset($_POST['rental_conditions']) && is_array($_POST['rental_conditions'])) ? implode(',', $_POST['rental_conditions']) : '';

    // Insert the main listing record with host_id
    $stmt = $conn->prepare("INSERT INTO listings 
        (title, type, price, bedrooms, bathrooms, address, city, state, zip, country, description, amenities, rental_conditions, host_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiissssssssi", $title, $type, $price, $bedrooms, $bathrooms, $address, $city, $state, $zip, $country, $description, $amenities, $rental_conditions, $host_id);

    if ($stmt->execute()) {
        // Get the ID of the newly created listing
        $listing_id = $stmt->insert_id;

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = "uploads/";
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $imageName = basename($_FILES['images']['name'][$key]);
                $targetPath = $uploadDir . uniqid() . "_" . $imageName;
                if (move_uploaded_file($tmpName, $targetPath)) {
                    // Insert the image path into the listing_images table
                    $imgStmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_url) VALUES (?, ?)");
                    $imgStmt->bind_param("is", $listing_id, $targetPath);
                    $imgStmt->execute();
                    $imgStmt->close();
                }
            }
        }

        // 🚀 Redirect to home.php after successful submission
        header("Location: home.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
