<?php
session_start();
header('Content-Type: application/json');

include 'db_connect.php';  // Adjust path as needed

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Default avatar if none found
    $avatar = 'images/default-avatar.jpg';
    
    // Prepare and execute query to fetch profile_pic from profile table
    $stmt = $conn->prepare("SELECT profile_pic FROM profile WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($profile_pic);
    if ($stmt->fetch() && !empty($profile_pic)) {
        $avatar = $profile_pic;  // Use profile pic from DB if available
    }
    $stmt->close();
    
    echo json_encode([
        'loggedIn' => true,
        'username' => $_SESSION['username'],
        'avatar' => $avatar
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
?>

