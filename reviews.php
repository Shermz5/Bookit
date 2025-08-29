<?php
include 'db_connect.php';  // assumes your database connection is in db_connect.php

session_start();
$host_id = $_SESSION['host_id'] ?? ($_SESSION['user_id'] ?? null);
if (!$host_id) {
    die('Error: Host not logged in.');
}

// Check if user is a host (has at least one listing)
$is_host = false;
$stmt = $conn->prepare("SELECT COUNT(*) FROM listings WHERE host_id = ?");
$stmt->bind_param("i", $host_id);
$stmt->execute();
$stmt->bind_result($listing_count);
$stmt->fetch();
$stmt->close();
if ($listing_count > 0) {
    $is_host = true;
}

if (!$is_host) {
    // Not a host, show message but keep layout
    $show_reviews = false;
} else {
    $show_reviews = true;

    // Only calculate and display review stats if user is a host
    $sql = "SELECT AVG(rating) AS average_rating FROM reviews";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $averageRating = round($row['average_rating'], 1);
    } else {
        $averageRating = "N/A";
    }
    // Get 5-star reviews
    $sqlFiveStar = "SELECT COUNT(*) as five_star_count FROM reviews WHERE rating = 5";
    $resultFiveStar = $conn->query($sqlFiveStar);
    $fiveStarCount = ($resultFiveStar && $row = $resultFiveStar->fetch_assoc()) ? $row['five_star_count'] : 0;
    // Get total reviews
    $sqlTotalReviews = "SELECT COUNT(*) as total_reviews FROM reviews";
    $resultTotalReviews = $conn->query($sqlTotalReviews);
    $totalReviews = ($resultTotalReviews && $row = $resultTotalReviews->fetch_assoc()) ? $row['total_reviews'] : 0;
}

$sql = "SELECT id, title FROM listings WHERE host_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);  // use $host_id here as well
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

$stmt->close();

$sql = "
SELECT 
    users.first_name, 
    users.last_name, 
    reviews.created_at, 
    listings.title AS property_name, 
    reviews.rating, 
    reviews.review_text
FROM 
    reviews
JOIN 
    users ON reviews.user_id = users.id
JOIN 
    listings ON reviews.listing_id = listings.id
WHERE 
    listings.host_id = ?
ORDER BY 
    reviews.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = []; // 💡 Initialize array
while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'created_at' => date('F j, Y', strtotime($row['created_at'])),
        'property_name' => $row['property_name'],
        'rating' => (int)$row['rating'],
        'review_text' => $row['review_text'],
    ];
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews - Landlord Dashboard</title>
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
                <a href="dashboard.php" class="sidebar-link ">
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
                <a href="reviews.php" class="sidebar-link active">
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
                <h1 class="text-2xl font-semibold">Reviews & Ratings</h1>
                <p class="text-gray-500">See what guests are saying about your properties.</p>
              </div>
              
              <?php if ($show_reviews): ?>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
               <div class="dashboard-card">
                  <div class="dashboard-card-content pt-6">
                    <div class="text-sm font-medium text-gray-500 mb-1">Average Rating</div>
                    <div class="flex items-end gap-2">
                      <div class="text-2xl font-bold">
                        <?php echo $averageRating; ?>
                      </div>
                      <div class="flex">
                        <?php
                        if ($averageRating !== "N/A") {
                            $fullStars = floor($averageRating);
                            $halfStar = ($averageRating - $fullStars) >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;

                            for ($i = 0; $i < $fullStars; $i++) {
                              echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="yellow" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                            }

                            if ($halfStar) {
                              echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="yellow" stroke="yellow" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                            }

                            for ($i = 0; $i < $emptyStars; $i++) {
                              echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                            }
                        } else {
                            echo '<span class="text-gray-400">No rating available</span>';
                        }
                        ?>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="dashboard-card">
                  <div class="dashboard-card-content pt-6">
                    <div class="text-sm font-medium text-gray-500 mb-1">5 Star Reviews</div>
                    <div class="text-2xl font-bold"><?php echo $fiveStarCount; ?></div>
                  </div>
                </div>

                <div class="dashboard-card">
                  <div class="dashboard-card-content pt-6">
                    <div class="text-sm font-medium text-gray-500 mb-1">Total Reviews</div>
                    <div class="text-2xl font-bold"><?php echo $totalReviews; ?></div>
                  </div>
                </div>

              </div>
              <?php endif; ?>
              
              <div class="flex flex-col sm:flex-row gap-4 items-center mb-6">
                <div class="relative flex-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                  <input 
                    type="search" 
                    placeholder="Search reviews..." 
                    class="w-full border rounded-md px-10 py-2"
                  />
                </div>


                <div>
                  <select class="select-filter border rounded-md px-3 py-2">
                    <option value="all">All Properties</option>
                    <?php foreach ($properties as $property): ?>
                      <option value="<?php echo htmlspecialchars($property['id']); ?>">
                        <?php echo htmlspecialchars($property['title']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>


              </div>
              
              <div class="space-y-4">
                <!-- Review Item 1 -->
                <div class="container mx-auto p-4">
  <?php if (!$show_reviews): ?>
    <div style="margin:2em; color:#888; text-align:center; font-size:1.2em;">You are not a host. No reviews to display.</div>
  <?php else: ?>
    <?php foreach ($reviews as $review): ?>
    <div class="dashboard-card mb-4">
      <div class="dashboard-card-content pt-6">
        <div class="flex justify-between mb-2">
          <div>
            <h3 class="font-semibold"><?= htmlspecialchars($review['full_name']) ?></h3>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($review['property_name']) ?></p>
          </div>
          <div class="text-sm text-gray-500"><?= htmlspecialchars($review['created_at']) ?></div>
        </div>
        <div class="mb-3">
          <div class="flex">
            <?php for ($i = 1; $i <= 5; $i++): ?>
    <?php if ($i <= $review['rating']): ?>
        <!-- Filled Star -->
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFD700" viewBox="0 0 24 24">
            <path d="M12 .587l3.668 7.571 8.332 1.151-6.001 5.873 1.415 8.269L12 18.896l-7.414 4.555 1.415-8.269-6.001-5.873 8.332-1.151z"/>
        </svg>
    <?php else: ?>
        <!-- Empty Star -->
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#FFD700" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 .587l3.668 7.571 8.332 1.151-6.001 5.873 1.415 8.269L12 18.896l-7.414 4.555 1.415-8.269-6.001-5.873 8.332-1.151z"/>
        </svg>
    <?php endif; ?>
<?php endfor; ?>

          </div>
        </div>
        <p class="text-gray-700"><?= htmlspecialchars($review['review_text']) ?></p>
      </div>
      <div class="dashboard-card-footer border-t pt-4 flex justify-end">
        <button class="btn btn-sm btn-outline">Reply</button>
      </div>
    </div>
  <?php endforeach; ?>
  <?php endif; ?>
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