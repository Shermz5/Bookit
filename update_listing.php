<?php
include 'db_connect.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate and sanitize ID
    $listing_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($listing_id <= 0) {
        die("Invalid listing ID.");
    }

    // Collect and sanitize the rest of the form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $price = floatval($_POST['price']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $zip = mysqli_real_escape_string($conn, $_POST['zip']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Handle amenities (array of checkboxes)
    $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];
    $amenities_str = implode(',', array_map('mysqli_real_escape_string', array_fill(0, count($amenities), $conn), $amenities));

    // Handle rental conditions (array of checkboxes)
    $rental_conditions = isset($_POST['rental_conditions']) ? $_POST['rental_conditions'] : [];
    $rental_conditions_str = implode(',', array_map('mysqli_real_escape_string', array_fill(0, count($rental_conditions), $conn), $rental_conditions));

    // Handle image upload (optional)
    // For simplicity, assume no image update unless the user uploads new images
    $image_paths = [];
    if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && $_FILES['images']['name'][0] != "") {
        $upload_dir = 'uploads/';
        for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
            $tmp_name = $_FILES['images']['tmp_name'][$i];
            $file_name = basename($_FILES['images']['name'][$i]);
            $target_path = $upload_dir . time() . '_' . $file_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $image_paths[] = $target_path;
            }
        }
    }

    // Update the listing in the database
    $sql = "UPDATE listings SET 
                title = '$title',
                type = '$type',
                price = '$price',
                bedrooms = '$bedrooms',
                bathrooms = '$bathrooms',
                address = '$address',
                city = '$city',
                state = '$state',
                zip = '$zip',
                country = '$country',
                description = '$description',
                amenities = '$amenities_str',
                rental_conditions = '$rental_conditions_str'";

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assume $listing_id is sanitized and defined here
    // Also assume other fields like $title, $description, $price etc. are set

    // Main UPDATE query (excluding images)
    $sql = "UPDATE listings SET 
                title = '$title', 
                description = '$description', 
                price = '$price'
            WHERE id = '$listing_id'";

    if (mysqli_query($conn, $sql)) {
        // Only update images if new images are uploaded
        if (!empty($_FILES['images']['name'][0])) {
            // Delete old images from listing_images table
            $deleteStmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ?");
            $deleteStmt->bind_param("i", $listing_id);
            $deleteStmt->execute();
            $deleteStmt->close();

            // Handle image uploads
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
        } else {
            // If there are no images for this listing, prompt user to upload at least one
            $checkImgStmt = $conn->prepare("SELECT COUNT(*) FROM listing_images WHERE listing_id = ?");
            $checkImgStmt->bind_param("i", $listing_id);
            $checkImgStmt->execute();
            $checkImgStmt->bind_result($imgCount);
            $checkImgStmt->fetch();
            $checkImgStmt->close();
            if ($imgCount == 0) {
                echo "<script>alert('Please upload at least one image for your listing.'); window.history.back();</script>";
                exit;
            }
        }
        echo "<script>alert('Property updated successfully.'); window.location.href = 'listings.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
}

?>
