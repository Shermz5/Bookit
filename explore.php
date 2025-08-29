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

    .explore-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2em;
    margin-bottom: 2em;
    justify-items: start;
    align-items: start;
  }
  .explore-grid > .card.relative {
    justify-self: start;
    align-self: start;
  }
  @media (max-width: 900px) {
    .explore-grid { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 600px) {
    .explore-grid { grid-template-columns: 1fr; }
    .card.relative img { height: 140px; }
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
                  <button type="button" class="btn btn-outline btn-search" onclick="window.location.href='search_display.php'">
                    <i class="fas fa-sliders-h"></i>
                    Refine Search
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </section>
      
    <?php
include 'db_connect.php';

// Fetch all listings
$sql = "SELECT * FROM listings";
$result = $conn->query($sql);

// Create a pool of featured listing IDs
$featuredIds = [];
if ($result && $result->num_rows > 6) {
    $allIds = [];
    while ($row = $result->fetch_assoc()) {
        $allIds[] = $row['id'];
    }
    shuffle($allIds);
    $featuredIds = array_slice($allIds, 0, 6);

    // Re-fetch data to loop again
    $result = $conn->query($sql);
}

if ($result && $result->num_rows > 0) {
    // 🟢 Start the grid container with 5 columns
    echo '<div class="grid grid-cols-5 gap-6">';
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $isFeatured = in_array($id, $featuredIds);
        $featured = $isFeatured ? '<span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">Featured</span>' : '';

        $type = ucfirst($row['type']);
        $title = htmlspecialchars($row['title']);
        
        $parts = array_filter([
            $row['country'] ?? '',
            $row['city'] ?? '',
            $row['state'] ?? '',
            $row['address'] ?? ''
        ]);
        $location = htmlspecialchars(implode(', ', $parts));

        $price = number_format($row['price'], 0);

        // Fetch the first image from listing_images table
        $stmt_img = $conn->prepare("SELECT image_url FROM listing_images WHERE listing_id = ? ORDER BY id ASC LIMIT 1");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $result_img = $stmt_img->get_result();
        $image = 'default-placeholder.jpg'; // fallback image
        if ($img_row = $result_img->fetch_assoc()) {
            $image = htmlspecialchars($img_row['image_url']);
        }
        $stmt_img->close();

        // Fetch rating and review count from ratings table
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

        // 🟢 Your unchanged card HTML output
        echo <<<HTML
<div class="card relative">
    <a href="property.php?id=$id" style="text-decoration:none;">
        <div class="relative">
            <img src="$image" alt="$title" />
            $featured
            <span class="label type">$type</span>
            <span class="label rating">$rating ($review)</span>
        </div>
        <h3>$title</h3>
        <p class="location">$location</p>
        <p class="price">$$price <span>/month</span></p>
    </a>
</div>
HTML;
    }

    // 🟢 Close the grid container
    echo '</div>';
} else {
    echo "<p>No listings found.</p>";
}

$conn->close();
?>


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