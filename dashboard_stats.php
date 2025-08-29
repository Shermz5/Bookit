<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Calculate Monthly Revenue for this user (as owner)
$sql_revenue = "SELECT SUM(amount) AS monthly_revenue FROM payment_records WHERE owner_id = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($sql_revenue);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($monthly_revenue);
$stmt->fetch();
$stmt->close();
$monthly_revenue = $monthly_revenue ?: 0;

// Calculate Average Rating for this user (reviews posted about this user by others)
$sql_rating = "SELECT AVG(rating) AS avg_rating FROM reviews WHERE user_id = ?";
$stmt = $conn->prepare($sql_rating);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($avg_rating);
$stmt->fetch();
$stmt->close();
$avg_rating = $avg_rating ? round($avg_rating, 2) : 'N/A';
?>
<div class="stat-card">
  <div class="flex justify-between items-center mb-2">
    <h3 class="text-sm font-medium text-gray-500">Monthly Revenue</h3>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
      class="text-gray-400">
      <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
      <line x1="1" y1="10" x2="23" y2="10"></line>
    </svg>
  </div>
  <div class="text-2xl font-bold">$<?= number_format($monthly_revenue, 2) ?></div>
</div>

<div class="stat-card">
  <div class="flex justify-between items-center mb-2">
    <h3 class="text-sm font-medium text-gray-500">Average Rating</h3>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
      class="text-gray-400">
      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
    </svg>
  </div>
  <div class="text-2xl font-bold"><?= $avg_rating ?></div>
</div>
