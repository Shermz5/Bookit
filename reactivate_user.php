<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Start transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Move user back to users table
        $conn->query("INSERT INTO users SELECT * FROM suspended_users WHERE id = $user_id");

        // Delete user from suspended_users
        $conn->query("DELETE FROM suspended_users WHERE id = $user_id");

        $conn->commit();

        echo "<script>alert('User reactivated successfully.'); window.location.href='manage_users.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error reactivating user.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid user ID.'); window.history.back();</script>";
}

$conn->close();
?>
