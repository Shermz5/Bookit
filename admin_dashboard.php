<?php
// Database connection established
include 'db_connect.php'; 


// Query the total listings
$stmt = $conn->prepare("SELECT COUNT(*) AS total_listings FROM listings WHERE host_id = ?");
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_properties = $row['total_listings'] ?? 0;

// Calculate increase from last month
$lastMonthQuery = $conn->prepare("
    SELECT COUNT(*) AS last_month_total 
    FROM listings 
    WHERE host_id = ? 
    AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
    AND YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
");
$lastMonthQuery->bind_param("i", $host_id);
$lastMonthQuery->execute();
$lastMonthResult = $lastMonthQuery->get_result();
$lastMonthRow = $lastMonthResult->fetch_assoc();

$last_month_total = $lastMonthRow['last_month_total'] ?? 0;
$increase = $total_properties - $last_month_total;

// Query active bookings count
$stmt = $conn->prepare("
    SELECT COUNT(*) AS active_bookings 
    FROM bookings 
    JOIN listings ON bookings.listing_id = listings.id
    WHERE listings.host_id = ? 
    AND bookings.status IN ('confirmed', 'pending')
");
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$active_bookings = $row['active_bookings'] ?? 0;

// Calculate increase from previous month
$lastMonthQuery = $conn->prepare("
    SELECT COUNT(*) AS last_month_active 
    FROM bookings 
    JOIN listings ON bookings.listing_id = listings.id
    WHERE listings.host_id = ?
    AND bookings.status IN ('confirmed', 'pending')
    AND MONTH(bookings.move_in) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
    AND YEAR(bookings.move_in) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
");
$lastMonthQuery->bind_param("i", $host_id);
$lastMonthQuery->execute();
$lastMonthResult = $lastMonthQuery->get_result();
$lastMonthRow = $lastMonthResult->fetch_assoc();

$last_month_active = $lastMonthRow['last_month_active'] ?? 0;
$increase = $active_bookings - $last_month_active;

// Fetch the latest 3 bookings for this host
$stmt = $conn->prepare("
    SELECT 
        listings.title AS property_name,
        bookings.move_in,
        bookings.move_out,
        bookings.price
    FROM bookings
    JOIN listings ON bookings.listing_id = listings.id
    WHERE listings.host_id = ?
    ORDER BY bookings.move_in DESC
    LIMIT 3
");

$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();

$recent_bookings = [];
while ($row = $result->fetch_assoc()) {
    $recent_bookings[] = $row;
}

$stmt->close();
$conn->close();

?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
   <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>

  
</head>
<body>
  <div class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="site-header">
      <div class="container">
        <div class="header-inner">
          <div class="logo">
            <a href="index.html">
              <span class="logo-text">Bookit</span>
            </a>
          </div>
          
          <nav class="desktop-nav">
            <a href="dashboard.php" class="nav-link active">Dashboard</a>
          </nav>
          
          <div class="auth-buttons">
            <a  class="btn btn-ghost" href="logout.php" >Logout</a>
          </div>
          
          <button class="mobile-menu-button" id="mobileMenuButton">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Sidebar Navigation -->
          
          

  <!-- Admin Dashboard Container -->
  <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Property Management Card -->
    <div class="bg-white p-4 rounded-2xl shadow hover:shadow-lg transition">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M3 10l9-7 9 7v10a1 1 0 01-1 1H4a1 1 0 01-1-1V10z"/>
            <path d="M9 21V9h6v12"/>
          </svg>
          Properties
        </h2>
      </div>
      <p class="text-gray-600 text-sm">
        Manage, add, or edit all listed properties across the platform.
      </p>
      <a href="manage_properties.php" class="mt-4 inline-block w-full text-center bg-primary text-white py-2 rounded hover:bg-primary-dark transition">
        Manage Properties
      </a>
    </div>

    <!-- User Management Card -->
    <div class="bg-white p-4 rounded-2xl shadow hover:shadow-lg transition">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M16 14a4 4 0 00-8 0"/>
            <path d="M12 6a4 4 0 00-4 4v1a4 4 0 008 0V10a4 4 0 00-4-4z"/>
          </svg>
          Users
        </h2>
      </div>
      <p class="text-gray-600 text-sm">
        View, approve, suspend user accounts, and address user queries (tenants, landlords).
      </p>
      <a href="manage_users.php" class="mt-4 inline-block w-full text-center bg-primary text-white py-2 rounded hover:bg-primary-dark transition">
        Manage Users
      </a>
    </div>

    <!-- Bookings Management Card -->
    <div class="bg-white p-4 rounded-2xl shadow hover:shadow-lg transition">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M5 4v16h14V4H5zm2 2h10v12H7V6z"/>
          </svg>
          Bookings
        </h2>
      </div>
      <p class="text-gray-600 text-sm">
        Monitor and manage all bookings, payments, and cancellations.
      </p>
      <a href="manage_bookings.php" class="mt-4 inline-block w-full text-center bg-primary text-white py-2 rounded hover:bg-primary-dark transition">
        View Bookings
      </a>
    </div>

    <!-- Earnings Reports Card -->
    <div class="bg-white p-4 rounded-2xl shadow hover:shadow-lg transition">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold flex items-center gap-2 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 8v4l3 3"/>
            <path d="M20 12a8 8 0 11-16 0 8 8 0 0116 0z"/>
          </svg>
          Earnings
        </h2>
      </div>
      <p class="text-gray-600 text-sm">
        Generate earnings reports and analyze platform revenue.
      </p>
      <a href="earnings_reports.php" class="mt-4 inline-block w-full text-center bg-primary text-white py-2 rounded hover:bg-primary-dark transition">
        View Reports
      </a>
    </div>

  </div>




        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-column">
            <a href="index.html" class="footer-logo">Bookit</a>
            <p class="footer-description">
              Making property rental easy and accessible for everyone.
            </p>
          </div>
          
          <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
              <li><a href="index.html">Home</a></li>
              <li><a href="explore.php">Explore</a></li>
              <li><a href="dashboard.php">Dashboard</a></li>
              <li><a href="submit.html">List Property</a></li>
            </ul>
          </div>
          
          <div class="footer-column">
            <h3>Get in Touch</h3>
            <ul class="footer-links">
              <li><a href="#">About Us</a></li>
              <li><a href="#">Contact Us</a></li>
              <li><a href="#">Privacy Policy</a></li>
              <li><a href="#">Terms of Service</a></li>
            </ul>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>© 2025 Bookit. All rights reserved.</p>
          <div class="social-icons">
            <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg></a>
            <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
            <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg></a>
          </div>
        </div>
      </div>
    </footer>
  </div>
  
  <script src="app.js"></script>
</body>
</html>
