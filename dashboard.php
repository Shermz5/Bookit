<?php
// Database connection established
include 'db_connect.php'; 

session_start(); // make sure session is started

// Retrieve host_id from session
if (isset($_SESSION['user_id'])) {
    $host_id = intval($_SESSION['user_id']);
} else {
    die("Error: Host not logged in.");
}

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
            <a href="home.php" class="nav-link">Home</a>
            <a href="explore.php" class="nav-link">Explore</a>
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
          <aside class="w-full md:w-64 shrink-0">
            <div class="bg-white rounded-lg shadow p-4">
              <div class="mb-6">
                <h2 class="font-semibold text-xl text-gray-800">Portal</h2>
              </div>
              <nav class="space-y-1">
                <a href="dashboard.php" class="sidebar-link active">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                  Dashboard
                </a>
                <a href="listings.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3" y2="6"></line><line x1="3" y1="12" x2="3" y2="12"></line><line x1="3" y1="18" x2="3" y2="18"></line></svg>
                  Listings
                </a>
                <a href="payments.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                  Payments
                </a>
                <a href="bookings.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                  Bookings
                </a>
                <a href="reviews.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                  Reviews
                </a>
                <a href="profile.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  Profile
                </a>
              </nav>
            </div>
          </aside>
          
          <!-- Main Content -->
<main class="flex-1 bg-white rounded-lg shadow p-6">
  <div class="space-y-6">
    <div class="flex justify-between items-start">
      <div>
        <h1 class="text-2xl font-semibold">Landlord Dashboard</h1> 
        <a href="tenant_dashboard.php"
                 class="inline-block px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                Go to Tenant Dashboard →
              </a>
          <p class="text-gray-500 mb-4">Welcome back! Here's an overview of your properties.</p>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Landlord Stat Cards -->
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


            <div class="stat-card">
              <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm font-medium text-gray-500">Active Bookings</h3>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="text-gray-400">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
              </div>
              <div class="text-2xl font-bold">
                <?php echo $active_bookings; ?>
              </div>
              <p class="text-xs text-gray-500">
                <?php echo ($increase >= 0 ? '+' : ''); ?><?php echo $increase; ?> from last month
              </p>
            </div>

            

          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
  <div class="dashboard-card">
    <div class="dashboard-card-header">
      <h3 class="text-lg font-semibold">Recent Bookings</h3>
    </div>
    <div class="dashboard-card-content">
      <div class="space-y-4">
        <?php if (!empty($recent_bookings)): ?>
          <?php foreach ($recent_bookings as $booking): ?>
            <div class="flex items-center justify-between border-b pb-3">
              <div>
                <p class="font-medium"><?php echo htmlspecialchars($booking['property_name']); ?></p>
                <p class="text-sm text-gray-500">
                  <?php 
                    echo date('M j, Y', strtotime($booking['move_in'])) . " - " . date('M j, Y', strtotime($booking['move_out']));
                  ?>
                </p>
              </div>
              <div>
                <span class="font-semibold">$<?php echo number_format($booking['price'], 2); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No recent bookings found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


       
      </div>
    </div>
  </div>
</main>


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
  
  
</body>
</html>
