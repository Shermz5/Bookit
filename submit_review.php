<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        die('You must be logged in to submit a review.');
    }

    $user_id = $_SESSION['user_id'];
    $listing_id = $_POST['listing_id'];
    $rating = floatval($_POST['rating']);
    $review_text = trim($_POST['review_text']);

    $sql = "
        INSERT INTO reviews (listing_id, user_id, rating, review_text)
        VALUES (?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iids", $listing_id, $user_id, $rating, $review_text);
    $stmt->execute();
    $stmt->close();

    header("Location: property.php?id=$listing_id");
    exit();
}
?>
