<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookit</title>
    <meta name="description" content="Find and book your perfect accommodation anywhere in the world." />
    <meta name="author" content="Bookit" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="property.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            <a href="home.php" class="active">Home</a>
            <a href="explore.php">Explore</a>
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
            <a href="submit.html">I'm a Landlord</a>
            <div class="mobile-auth">
              <button onclick="window.location.href='login.html';" class="btn btn-outline btn-full">Login</button>
              <button onclick="window.location.href='signup.html';" class="btn btn-primary btn-full">Sign Up</button>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <main>
      <!-- Hero Section -->
      <section class="hero">
        <div class="container">
          <div class="hero-content">
            <h1>Connecting Tenants with Trusted Landlords</h1>
            <p>Bookit makes renting simple, secure, and stress-free - whether you're listing a property or looking for your next space</p>
          </div>

          <div class="search-box">
            <form class="search-form" action="search_display.php" method="get">
              <div class="search-grid">
                <div class="search-item">
                  <label>Where</label>
                  <div class="input-icon">
                    <i class="fas fa-search"></i>
                    <input type="text" name="location" placeholder="Location">
                  </div>
                </div>

                <div class="search-item">
                  <label>When</label>
                  <div class="input-icon">
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="date">
                  </div>
                </div>

                <div class="search-item">
                  <label>Price</label>
                  <select name="price">
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
                  <button type="button" class="btn btn-outline btn-search" onclick="window.location.href='explore.php'">
                    <i class="fas fa-sliders-h"></i>
                    Refine Search
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Featured Properties Section -->
      <?php

include 'db_connect.php'; // assumes you have this connection script

// Fetch featured properties
$sql = "
    SELECT listings.id, listings.title, listings.price, MIN(listing_images.image_url) AS image_url
    FROM listings
    JOIN listing_images ON listings.id = listing_images.listing_id
    GROUP BY listings.id, listings.title, listings.price
    ORDER BY RAND()
    LIMIT 6
";




$result = $conn->query($sql);
?>

<section class="properties-section">
  <div class="container">
    <div class="section-header">
      <div>
        <h2>Featured Properties</h2>
        <p>Discover our hand-picked selection of exceptional accommodations</p>
      </div>
      <div class="section-actions">
        <button class="btn btn-circle" id="%">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="btn btn-circle" id="nextProperty">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <div class="property-grid" id="featuredPropertiesGrid">
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <div class="card relative">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Property Image">

            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p class="price">$<?= number_format($row['price']) ?>/Month</p>
            <a href="property.php?id=<?= $row['id'] ?>" class="btn btn-primary">View Details</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No featured properties available right now.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

          <div class="view-all">
            <a href="explore.php" class="btn btn-outline">View All Properties</a>
          </div>
        </div>
      </section>

   <!-- Popular Locations -->
    <?php
// index.php (or home.php)

// Include your DB connection and fetch locations data
include 'db_connect.php';

$sql = "SELECT city, COUNT(*) AS total FROM listings WHERE city IS NOT NULL AND city != '' GROUP BY city ORDER BY total DESC LIMIT 6";
$result = $conn->query($sql);

$cityImages = [
    'Harare' => 'cities/harare.jpeg',
    'Bulawayo' => 'cities/bulawayo.jpeg',
    'Gweru' => 'cities/gweru.jpeg',
    'Kwekwe' => 'cities/kwekwe.jpeg',
    'Mutare' => 'cities/mutare.jpeg',
    'Chinhoyi' => 'cities/chinhoyi.jpeg',
    'Bindura' => 'cities/bindura.jpeg',
    'Victoria Falls' => 'cities/victoriafalls.jpg',
];

$locations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $city = htmlspecialchars($row['city']);
        $img = $cityImages[$city] ?? 'homepage.jpg';
        $locations[] = [
            'city' => $city,
            'total' => intval($row['total']),
            'image' => $img
        ];
    }
}

$conn->close();
?>

      <section class="locations-section bg-gray">
  <div class="container">
    <div class="section-header centered">
      <h2>Popular Locations</h2>
      <p>Explore our most sought-after locations around</p>
    </div>
    <div class="cards-grid">
      <?php if (!empty($locations)): ?>
        <?php foreach ($locations as $loc): ?>
          <a href="search_display.php?location=<?= urlencode($loc['city']) ?>" class="location-card">
            <img src="<?= $loc['image'] ?>" alt="<?= $loc['city'] ?>">
            <div class="card-content">
              <h3><?= $loc['city'] ?></h3>
              <p><?= $loc['total'] ?> properties available</p>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No popular locations available at the moment.</p>
      <?php endif; ?>
    </div>
  </div>
</section>



          
        </div>
      </section>

      <!-- Benefits Section -->
      <section class="benefits-section">
        <div class="container">
          <div class="section-header centered">
            <h2>Why Choose Bookit</h2>
            <p>We make finding and booking accommodations easy and worry-free</p>
          </div>

          <div class="benefits-grid">
            <div class="benefit-card">
              <div class="benefit-icon"><i class="fas fa-search"></i></div>
              <h3>Easy Booking</h3>
              <p>Find and book your perfect stay in just a few clicks with our intuitive search and filtering system.</p>
            </div>
            <div class="benefit-card">
              <div class="benefit-icon"><i class="fas fa-home"></i></div>
              <h3>Legit Properties</h3>
              <p>All our listings are carefully vetted to ensure accuracy of information.</p>
            </div>
            <div class="benefit-card">
              <div class="benefit-icon"><i class="fas fa-users"></i></div>
              <h3>Customer Support</h3>
              <p>Our dedicated support team is always available to assist you.</p>
            </div>
            <div class="benefit-card">
              <div class="benefit-icon"><i class="fas fa-shield-alt"></i></div>
              <h3>Secure Payments</h3>
              <p>Your transactions are protected with us.</p>
            </div>
          </div>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-column">
            <a href="home.php" class="footer-logo">Bookit</a>
            <p class="footer-description">
              Find and book your perfect accommodation anywhere. From luxury homes to cozy rooms, we've got you covered.
            </p>
          </div>
          <div class="footer-column">
            <h3>Quick Links</h3>
            <ul class="footer-links">
              <li><a href="home.php">Home</a></li>
              <li><a href="explore.php">Explore</a></li>
              <li><a href="about.php">About Us</a></li>
            </ul>
          </div>
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
