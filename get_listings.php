<?php
require 'db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT title, type, price, city, state, country, bedrooms, bathrooms, description, images FROM listings";
$result = $conn->query($sql);

$listings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Decode JSON if images are stored as JSON array
        $images = json_decode($row['images'], true);
        $row['image_url'] = $images[0] ?? 'default.jpg'; // use first image or fallback
        $listings[] = $row;
    }
}

echo json_encode($listings);
$conn->close();
?>
