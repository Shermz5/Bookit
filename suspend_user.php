<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Move user to suspended_users
        $conn->query("INSERT INTO suspended_users SELECT * FROM users WHERE id = $user_id");

        // Delete user from users table
        $conn->query("DELETE FROM users WHERE id = $user_id");

        $conn->commit();
        echo "<script>alert('User suspended (moved) successfully.'); window.location.href='manage_users.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error suspending user.'); window.history.back();</script>";
    }

} else {
    echo "<script>alert('Invalid user ID.'); window.history.back();</script>";
}

$conn->close();
?>
