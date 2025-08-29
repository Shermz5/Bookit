<?php
// delete_listing.php

// Include the database connection file
include 'db_connect.php';

// Check if 'id' is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $listing_id = intval($_GET['id']);

    // Prepare the DELETE query
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $listing_id);
        if ($stmt->execute()) {
            // Successfully deleted
            header("Location: listings.php?message=Listing deleted successfully");
            exit();
        } else {
            echo "Error deleting listing: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing the delete statement: " . $conn->error;
    }
} else {
    echo "Invalid request. No valid ID provided.";
}

// Close the database connection
$conn->close();
?>
