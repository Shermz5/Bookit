<?php
session_start();

// Include your DB connection
include 'db_connect.php';

// Check if the user is an admin
// (You may want to add your admin check logic here)

// Fetch all bookings with property and host information
$bookingQuery = $conn->prepare("
    SELECT bookings.id, bookings.move_in, bookings.move_out, bookings.price, 
           bookings.status, users.first_name AS tenant_first_name, users.last_name AS tenant_last_name,
           listings.title AS property_title, host.first_name AS host_first_name, host.last_name AS host_last_name
    FROM bookings 
    JOIN users ON bookings.user_id = users.id
    JOIN listings ON bookings.listing_id = listings.id
    JOIN users AS host ON listings.host_id = host.id
");
$bookingQuery->execute();
$bookingResult = $bookingQuery->get_result();
$bookings = $bookingResult->fetch_all(MYSQLI_ASSOC);

// Fetch payment records (fix join to use booking_id)
$paymentQuery = $conn->prepare("
    SELECT payment_records.id, payment_records.amount, payment_records.created_at, 
           payment_records.booking_id, bookings.status 
    FROM payment_records 
    JOIN bookings ON payment_records.booking_id = bookings.id
");
$paymentQuery->execute();
$paymentResult = $paymentQuery->get_result();
$payments = $paymentResult->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Management - Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script>
    function toggleView(view) {
      document.querySelectorAll('.toggle-section').forEach(section => {
        section.style.display = 'none';
      });
      document.getElementById(view).style.display = 'block';
    }
  </script>
</head>
<body>
  <div class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="site-header">
      <div class="container">
        <div class="header-inner">
          <div class="logo">
            <a href="admin_dashboard.php">
              <span class="logo-text">Admin Panel</span>
            </a>
          </div>
          <nav class="desktop-nav">
            <a href="booking_management.php" class="nav-link active">Booking Management</a>
            <a href="logout.php" class="nav-link">Logout</a>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold">Booking Management</h1>
        
        <!-- Toggle Buttons -->
        <div class="mt-6 mb-4">
          <button onclick="toggleView('bookings')" class="btn">Bookings</button>
          <button onclick="toggleView('payments')" class="btn">Payments</button>
          <button onclick="toggleView('cancellations')" class="btn">Cancellations</button>
          <button onclick="toggleView('confirmations')" class="btn">Confirmations</button>
        </div>

        <!-- Bookings Section -->
        <div id="bookings" class="toggle-section">
          <h2 class="text-lg font-semibold">All Bookings</h2>
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100 border-b">
              <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Tenant Name</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Property Name</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Host Name</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move In</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move Out</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Price</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['tenant_first_name'] . ' ' . $booking['tenant_last_name']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['property_title']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['host_first_name'] . ' ' . $booking['host_last_name']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_in']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_out']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800">$<?php echo htmlspecialchars($booking['price']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Payments Section -->
        <div id="payments" class="toggle-section" style="display:none;">
          <h2 class="text-lg font-semibold">Payment Records</h2>
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100 border-b">
              <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Payment ID</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Booking ID</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Amount</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Date</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $payment): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($payment['id']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800">$<?php echo htmlspecialchars($payment['amount']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($payment['created_at']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($payment['status']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Cancellations Section -->
        <div id="cancellations" class="toggle-section" style="display:none;">
          <h2 class="text-lg font-semibold">Cancellations</h2>
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100 border-b">
              <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Tenant Name</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move In</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move Out</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Price</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <?php if ($booking['status'] === 'cancelled'): ?>
                  <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['tenant_first_name'] . ' ' . $booking['tenant_last_name']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_in']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_out']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800">$<?php echo htmlspecialchars($booking['price']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['status']); ?></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Confirmations Section -->
        <div id="confirmations" class="toggle-section" style="display:none;">
          <h2 class="text-lg font-semibold">Confirmed Bookings</h2>
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100 border-b">
              <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Tenant Name</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move In</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Move Out</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Price</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <?php if ($booking['status'] === 'confirmed'): ?>
                  <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['tenant_first_name'] . ' ' . $booking['tenant_last_name']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_in']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['move_out']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800">$<?php echo htmlspecialchars($booking['price']); ?></td>
                    <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($booking['status']); ?></td>
                  </tr>
                <?php endif; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <p>© 2025 Bookit. All rights reserved.</p>
      </div>
    </footer>
  </div>
</body>
</html>
