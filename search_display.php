<?php
// Database connection
include 'db_connect.php';

// Get search parameters
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$priceRange = isset($_GET['price']) ? $_GET['price'] : '';

// Base SQL
$sql = "
    SELECT 
        listings.*, 
        li.image_url
    FROM listings
    LEFT JOIN (
        SELECT 
            listing_id, 
            MIN(id) AS min_image_id
        FROM listing_images
        GROUP BY listing_id
    ) AS first_image ON listings.id = first_image.listing_id
    LEFT JOIN listing_images li ON first_image.min_image_id = li.id
    WHERE 1=1
";

// Append conditions dynamically
if (!empty($location)) {
    $sql .= " AND (
        listings.city LIKE ? 
        OR listings.country LIKE ? 
        OR listings.state LIKE ? 
        OR listings.address LIKE ?
    )";
}

switch ($priceRange) {
    case '1':
        $sql .= " AND listings.price < ?";
        break;
    case '2':
        $sql .= " AND listings.price BETWEEN ? AND ?";
        break;
    case '3':
        $sql .= " AND listings.price BETWEEN ? AND ?";
        break;
    case '4':
        $sql .= " AND listings.price > ?";
        break;
}

// Prepare statement
$stmt = $conn->prepare($sql);

// Bind parameters
$paramTypes = '';
$params = [];

// Location binding
if (!empty($location)) {
    $paramTypes .= 'ssss';
    $searchTerm = '%' . $location . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Price binding
switch ($priceRange) {
    case '1':
        $paramTypes .= 'd';
        $params[] = 60;
        break;
    case '2':
        $paramTypes .= 'dd';
        $params[] = 60;
        $params[] = 120;
        break;
    case '3':
        $paramTypes .= 'dd';
        $params[] = 121;
        $params[] = 200;
        break;
    case '4':
        $paramTypes .= 'd';
        $params[] = 200;
        break;
}

// Bind dynamically
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}

// Execute
$stmt->execute();
$result = $stmt->get_result();

// Collect listings into array
$listings = [];
if ($result && $result->num_rows > 0) {
    while ($house = $result->fetch_assoc()) {
        $id = $house['id'];
        $type = ucfirst($house['type']);
        $title = htmlspecialchars($house['title'] ?? 'No Title');

        // Compose derived location
        $parts = array_filter([
            $house['country'] ?? '',
            $house['city'] ?? '',
            $house['state'] ?? '',
            $house['address'] ?? ''
        ]);
        $derivedLocation = htmlspecialchars(implode(', ', $parts));

        // Price
        $price = number_format($house['price'], 0);

        // Featured (20% randomly)
        $isFeatured = rand(0, 4) === 0;

        // Image
        $image = !empty($house['image_url']) ? htmlspecialchars($house['image_url']) : 'default-placeholder.jpg';

        // Rating and reviews
        $stmt_rating = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE listing_id = ?");
        $stmt_rating->bind_param("i", $id);
        $stmt_rating->execute();
        $result_rating = $stmt_rating->get_result();
        if ($rating_row = $result_rating->fetch_assoc()) {
            $rating = $rating_row['avg_rating'] !== null ? number_format($rating_row['avg_rating'], 1) : '0.0';
            $review = intval($rating_row['review_count']) . ' reviews';
        } else {
            $rating = '0.0';
            $review = 'No reviews';
        }
        $stmt_rating->close();

        // Save to array
        $listings[] = [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'location' => $derivedLocation,
            'price' => $price,
            'featured' => $isFeatured,
            'image' => $image,
            'rating' => $rating,
            'review' => $review
        ];
    }
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Explore Properties - Bookit</title>
    <meta name="description" content="Find and book your perfect accommodation anywhere in the world." />
    <meta name="author" content="Bookit" />
    <link rel="stylesheet" href="style.css" />
     <link rel="stylesheet" href="property.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
   <style>

      .profile-info {
  display: flex;
  align-items: center;
  gap: 10px; /* space between username and profile picture */
}

      .profile-dropdown {
        position: relative;
        display: inline-block;
        align-items: center;  
        gap: 15px;             
     }


      .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 8px;
      }

      .profile-pic:hover {
        transform: scale(1.1);
      }

      .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        border: 1px solid #ddd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 10px;
        border-radius: 6px;
        min-width: 150px;
        z-index: 999;
      }

      .profile-dropdown:hover .dropdown-menu {
        display: block;
      }

      .dropdown-menu a {
        display: block;
        padding: 8px 12px;
        color: black;
        text-decoration: none;
      }

      .dropdown-menu a:hover {
        background-color: #f5f5f5;
      }

      .hidden {
        display: none;
      }

.username {
  font-weight: bold;
  color: #333;
}

    </style>

    <script>
  window.addEventListener('DOMContentLoaded', () => {
    const authButtons = document.getElementById('authButtons');

    fetch('auth-status.php')
  .then(response => response.json())
  .then(data => {
    if (data.loggedIn) {
      authButtons.innerHTML = `
        <div class="profile-dropdown">
  <div class="profile-info">
    <img src="${data.avatar}" alt="Profile" class="profile-pic">
    <span class="username">${data.username}</span>
  </div>
  <div class="dropdown-menu">
    <a href="submit.html">I'm a Landlord</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

      `;
    } else {
      authButtons.innerHTML = `
        <button class="btn btn-ghost" onclick="location.href='login.html'">Login</button>
        <button class="btn btn-primary" onclick="location.href='signup.html'">Sign Up</button>
      `;
    }
  })
  .catch(error => {
    console.error('Auth check failed:', error);
  });

  });
</script>


  </head>
  <body>
    <header class="site-header">
      <div class="container">
        <div class="header-inner">
          <div class="logo">
            <a href="home.php">Bookit</a>
          </div>
          
          <nav class="desktop-nav">
            <a href="home.php">Home</a>
            <a href="explore.php" class="active" >Explore</a>
            <a href="about.php">About</a>
          </nav>
          
        <div class="auth-buttons">
            <!-- Login/Profile dynamic section -->
            <span id="authButtons"></span>
          </div>

          
          <button class="mobile-menu-button" id="mobileMenuButton">
            <i class="fas fa-bars"></i>
          </button>
        </div>
      </div>
      
      <!-- Mobile Menu -->
      <div class="mobile-menu" id="mobileMenu">
        <div class="container">
          <div class="mobile-menu-header">
            <div class="logo">Bookit</div>
            <button class="mobile-menu-close" id="mobileMenuClose">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <nav class="mobile-nav">
            <a href="home.php">Home</a>
            <a href="explore.php">Explore</a>
            <a href="about.php">About</a>
            
            <div class="mobile-auth">
              <button class="btn btn-outline btn-full">Login</button>
              <button class="btn btn-primary btn-full">Sign Up</button>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <main>
      <section class="search-section">
        <div class="container">
          <h1>Find Your Perfect Property</h1>
          
          <div class="search-box">
            <form class="search-form" id="searchForm" action="search_display.php" method="get">
              <div class="search-grid">
                <div class="search-item">
                  <label>Where</label>
                  <div class="input-icon">
                    <i class="fas fa-search"></i>
                    <input type="text" name="location" id="locationInput" placeholder="Location">
                  </div>
                </div>
                
                <div class="search-item">
                  <label>When</label>
                  <div class="input-icon">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="date" id="dateInput">
                  </div>
                </div>
                
                <div class="search-item">
                  <label>Price</label>
                  <select name="price" id="priceInput">
                    <option value="">Price Range</option>
                    <option value="1">Less than $60</option>
                    <option value="2">$60 - $120</option>
                    <option value="3">$121 - $200</option>
                    <option value="4">$200+</option>
                  </select>
                </div>
                
                <div class="search-item">
                  <button type="submit" class="btn btn-primary btn-search">
                    <i class="fas fa-search"></i>
                    Search
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </section>
      
    <?php if (!empty($listings)): ?>
    <div class="grid grid-cols-5 gap-6">
        <?php foreach ($listings as $listing): ?>
            <div class="card relative">
                <a href="property.php?id=<?= $listing['id'] ?>" style="text-decoration:none;">
                    <div class="relative">
                        <img src="<?= $listing['image'] ?>" alt="<?= $listing['title'] ?>" />
                        <?php if ($listing['featured']): ?>
                            <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">Featured</span>
                        <?php endif; ?>
                        <span class="label type"><?= $listing['type'] ?></span>
                        <span class="label rating"><?= $listing['rating'] ?> (<?= $listing['review'] ?>)</span>
                    </div>
                    <h3><?= $listing['title'] ?></h3>
                    <p class="location"><?= $listing['location'] ?></p>
                    <p class="price">$<?= $listing['price'] ?> <span>/month</span></p>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No listings found matching your search criteria.</p>
<?php endif; ?>



    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <!-- Company Info -->
          <div class="footer-column">
            <a href="home.php" class="footer-logo">Bookit</a>
            <p class="footer-description">
              Find and book your perfect accommodation anywhere. From luxury homes to cozy rooms, we've got you covered.
            </p>
          </div>

          <!-- Quick Links -->
          <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
              <li><a href="home.php">Home</a></li>
              <li><a href="explore.php">Explore</a></li>
              <li><a href="about.php">About Us</a></li>
            </ul>
          </div>

          <!-- Support Links -->
          <div class="footer-column">
            <h3>Support</h3>
            <ul class="footer-links">
              <li><a href="help.html">Help Center</a></li>
              <li><a href="terms.html">Terms of Service</a></li>
              <li><a href="privacy.html">Privacy Policy</a></li>
              <li><a href="contact.html">Contact Us</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; 2025 Bookit. All rights reserved.</p>
          <div class="social-icons">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>
    </footer>

    <script src="https://kit.fontawesome.com/your-code-here.js" crossorigin="anonymous"></script>
    
  </body>
</html>