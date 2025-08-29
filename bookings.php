<?php
session_start();

// Include your DB connection
include 'db_connect.php';

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

// Retrieve host_id from session
$host_id = intval($_SESSION['user_id']);

try {
    // Fetch bookings with joined property and guest names for the logged-in host
    $query = "
        SELECT 
            bookings.id, 
            bookings.listing_id,
            listings.title AS property_name, 
            CONCAT(users.first_name, ' ', users.last_name) AS guest_name,
            bookings.move_in,
            bookings.move_out,
            bookings.occupants,
            bookings.price,
            bookings.status
        FROM bookings
        JOIN listings ON bookings.listing_id = listings.id
        JOIN users ON bookings.user_id = users.id
        WHERE listings.host_id = ?
        ORDER BY bookings.move_in DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $host_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }
} catch (Exception $e) {
    echo "<p>Error fetching bookings: " . $e->getMessage() . "</p>";
    $bookings = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookings - Landlord Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <header class="site-header">
      <div class="container">
        <div class="header-inner">
          <div class="logo">
            <a href="home.php">
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
                <a href="dashboard.php" class="sidebar-link">
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
                <a href="bookings.php" class="sidebar-link active">
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
              <div>
                <h1 class="text-2xl font-semibold">Landlord Section(Bookings)</h1>
                <a href="tenant_bookings.php"
   class="text-blue-600 hover:text-blue-800 underline text-sm font-medium transition">
  Tenant Section(click here)→
</a>
                <p class="text-gray-500">Manage all your property bookings and reservations.</p>
              </div>
              
              <div class="flex flex-col sm:flex-row gap-4 items-center mb-4">
                <div class="relative flex-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <input 
                    type="search" 
                    placeholder="Search bookings..." 
                    class="w-full border rounded-md px-10 py-2"
                  />
                </div>
                
                <div>
                  <select class="select-filter border rounded-md px-3 py-2">
                    <option value="all">All Statuses</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              </div>
              
              <div class="border rounded-md overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest Name</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Occupants</th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                       <tbody class="bg-white divide-y divide-gray-200">
                          <?php if (!empty($bookings)): ?>
                              <?php foreach ($bookings as $booking): ?>
                                  <tr>
                                      <td class="px-6 py-4 whitespace-nowrap font-medium">
                                          <?= htmlspecialchars($booking['property_name']) ?>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          <?= htmlspecialchars($booking['guest_name']) ?>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          <?= htmlspecialchars($booking['move_in']) ?> to <?= htmlspecialchars($booking['move_out']) ?>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          <?= htmlspecialchars($booking['occupants']) ?>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          $<?= htmlspecialchars(number_format($booking['price'], 2)) ?>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap">
                                          <?php
                                          $status = strtolower($booking['status']);
                                          $badgeClass = match ($status) {
                                              'confirmed' => 'bg-green-100 text-green-800',
                                              'pending' => 'bg-yellow-100 text-yellow-800',
                                              'cancelled' => 'bg-red-100 text-red-800',
                                              default => 'bg-gray-100 text-gray-800',
                                          };
                                          ?>
                                          <span class="px-2 py-1 rounded-full text-xs <?= $badgeClass ?>">
                                              <?= ucfirst($status) ?>
                                          </span>
                                      </td>
                                      <td class="px-6 py-4 whitespace-nowrap text-right">
                                          <?php
                                              $booking_id = $booking['id'];
                                              $status = $booking['status'];
                                          ?>

                                          <?php if ($status === 'pending'): ?>
                                              <form method="post" action="process_booking.php" style="display:inline;">
                                                  <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                  <input type="hidden" name="action" value="accept">
                                                  <button type="submit" class="btn btn-sm btn-outline text-green-600 border-green-600">
                                                      Accept
                                                  </button>
                                              </form>
                                              <form method="post" action="process_booking.php" style="display:inline;">
                                                  <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                  <input type="hidden" name="action" value="decline">
                                                  <button type="submit" class="btn btn-sm btn-outline text-red-600 border-red-600">
                                                      Decline
                                                  </button>
                                              </form>
                                          <?php else: ?>
                                              <a href="property.php?id=<?php echo $booking['listing_id']; ?>" class="btn btn-sm btn-outline">
    View Details
</a>
                                          <?php endif; ?>
                                      </td>
                                  </tr>
                              <?php endforeach; ?>
                          <?php else: ?>
                              <tr>
                                  <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                      No bookings found.
                                  </td>
                              </tr>
                          <?php endif; ?>
                          </tbody>
                </table>
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
            <a href="home.php" class="footer-logo">Bookit</a>
            <p class="footer-description">
              Making property rental easy and accessible for everyone.
            </p>
          </div>
          
          <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
              <li><a href="home.php">Home</a></li>
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