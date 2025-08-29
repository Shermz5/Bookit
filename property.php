<?php
session_start();  // Start the session at the very top

require 'db_connect.php';

// Get the property ID from URL parameter (e.g., ?id=5)
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Assign listing_id for use in the form
$listing_id = $property_id;

// Get user_id from session safely
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch listing details (including host_id)
$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$listing_result = $stmt->get_result();
$listing = $listing_result->fetch_assoc();
$stmt->close();

// Fallbacks in case data is missing
$price = isset($listing['price']) ? number_format($listing['price'], 2) : "N/A";

// Fetch average rating and review count
$stmt = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count
    FROM reviews
    WHERE listing_id = ?
");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$rating_result = $stmt->get_result();
if ($rating_row = $rating_result->fetch_assoc()) {
    $avg_rating = $rating_row['avg_rating'] !== null ? number_format($rating_row['avg_rating'], 1) : "N/A";
    $review_count = intval($rating_row['review_count']);
} else {
    $avg_rating = "N/A";
    $review_count = 0;
}
$stmt->close();

// Fetch images from listing_images table
$stmt = $conn->prepare("SELECT image_url FROM listing_images WHERE listing_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$images_result = $stmt->get_result();

$images = [];
while ($row = $images_result->fetch_assoc()) {
    $images[] = $row['image_url'];
}
$stmt->close();

// Retrieve amenities from listings table
$amenities = [];
if (!empty($listing['amenities'])) {
    $amenities = explode(',', $listing['amenities']);
    if (!is_array($amenities)) {
        $amenities = []; // fallback
    }
}

// Retrieve rental conditions from listings table
$house_rules = [];
if (!empty($listing['rental_conditions'])) {
    $house_rules = explode(',', $listing['rental_conditions']);
    // Clean whitespace and replace underscores
    $house_rules = array_map(function($rule) {
        return str_replace('_', ' ', trim($rule));
    }, $house_rules);
}

// Retrieve host information
$host_id = isset($listing['host_id']) ? intval($listing['host_id']) : 0;
if ($host_id > 0) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, bio, profile_pic FROM profile WHERE id = ?");
    $stmt->bind_param("i", $host_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $host_first_name = $host_last_name = $host_bio = $host_profile_pic = "";
    if ($host = $result->fetch_assoc()) {
        $host_first_name = htmlspecialchars($host['first_name']);
        $host_last_name = htmlspecialchars($host['last_name']);
        $host_bio = htmlspecialchars($host['bio']);
        $host_profile_pic = !empty($host['profile_pic']) ? htmlspecialchars($host['profile_pic']) : 'default-profile-pic.jpg';
    }

    $stmt->close();
} else {
    $host_first_name = "Unknown";
    $host_last_name = "";
    $host_bio = "No bio available.";
    $host_profile_pic = 'default-profile-pic.jpg';
}


$listing_id = $_GET['id'] ?? 0;

// Fetch existing reviews
$sql = "
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.listing_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>







<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Property Details - Bookit</title>
    <meta name="description" content="Find and book your perfect accommodation anywhere in the world." />
    <meta name="author" content="Bookit" />
    <link rel="stylesheet" href="style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
     <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#1E90FF',secondary:'#0A2463'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

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

    <style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        
        .gallery-thumbnails::-webkit-scrollbar {
            height: 6px;
        }
        .gallery-thumbnails::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .gallery-thumbnails::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        .gallery-thumbnails::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .custom-checkbox {
            display: inline-block;
            position: relative;
            cursor: pointer;
            user-select: none;
        }
        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 4px;
        }
        .custom-checkbox:hover input ~ .checkmark {
            border-color: #cbd5e1;
        }
        .custom-checkbox input:checked ~ .checkmark {
            background-color: #1E90FF;
            border-color: #1E90FF;
        }
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }
        .custom-checkbox .checkmark:after {
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        </style>
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
            <a href="explore.php">Explore</a>
            <a href="about.php">About</a>
          </nav>
          
          
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
            
            <div class="auth-buttons">
            <!-- Login/Profile dynamic section -->
            <span id="authButtons"></span>
          </div>
          </nav>
        </div>
      </div>
    </header>

    <main>
      <div class="container" id="propertyDetailContainer">
        <div class="back-button">
          <button class="btn btn-ghost" id="backButton">
            <i class="fas fa-arrow-left"></i>
            Back to results
          </button>
        </div>
        
        <body class="bg-gray-50">
  
    <main class="container mx-auto px-4 py-6">
        
         <!-- Property Gallery -->
<div class="mb-8">
    <div class="relative">
        <div class="relative h-[450px] overflow-hidden rounded-lg">
            <div class="absolute top-3 left-3 z-10">
                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                    Available
                </span>
            </div>
            <div class="absolute top-3 right-3 z-10">
                <button class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-gray-100 transition-colors">
                    <i class="ri-heart-line text-gray-700"></i>
                </button>
            </div>
            <img id="mainImage" src="<?php echo htmlspecialchars($images[0] ?? 'default.jpg'); ?>" alt="Main Image" class="w-full h-full object-cover">

            <button class="absolute left-4 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors" id="prevBtn">
                <i class="ri-arrow-left-s-line text-gray-700"></i>
            </button>
            <button class="absolute right-4 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/80 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors" id="nextBtn">
                <i class="ri-arrow-right-s-line text-gray-700"></i>
            </button>
        </div>

        <div class="gallery-thumbnails mt-4 flex gap-2 overflow-x-auto pb-2">
            <?php foreach ($images as $index => $imgUrl): ?>
                <div class="thumbnail-item cursor-pointer rounded-md overflow-hidden h-20 w-32 flex-shrink-0 border-2 <?php echo $index === 0 ? 'border-primary' : 'border-transparent'; ?>">
                    <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="h-full w-full object-cover">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
const mainImage = document.getElementById('mainImage');
const thumbnails = document.querySelectorAll('.thumbnail-item img');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

let currentIndex = 0;

thumbnails.forEach((thumb, index) => {
    thumb.addEventListener('click', () => {
        mainImage.src = thumb.src;
        currentIndex = index;
        highlightThumbnail();
    });
});

prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
    mainImage.src = thumbnails[currentIndex].src;
    highlightThumbnail();
});

nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % thumbnails.length;
    mainImage.src = thumbnails[currentIndex].src;
    highlightThumbnail();
});

function highlightThumbnail() {
    thumbnails.forEach((thumb, index) => {
        thumb.parentElement.classList.toggle('border-primary', index === currentIndex);
        thumb.parentElement.classList.toggle('border-transparent', index !== currentIndex);
    });
}
</script>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Property Details -->
             <div class="lg:col-span-2">
               <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
              <?php echo htmlspecialchars($listing['title']); ?></h1>

                 <div class="flex items-center text-gray-600 mb-4">
                      <div class="w-5 h-5 flex items-center justify-center mr-1">
                         <i class="ri-map-pin-line"></i>
                </div>
        <span>
        <?php
        echo htmlspecialchars($listing['address']) . ', ' . 
             htmlspecialchars($listing['city']) . ', ' . 
             htmlspecialchars($listing['state']) . ', ' . 
             htmlspecialchars($listing['country']);
        ?>
       </span>
        </div>

<div class="flex flex-wrap gap-6 py-4 border-t border-b border-gray-200 mb-6">
    <div class="flex items-center">
        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
            <i class="ri-hotel-bed-line"></i>
        </div>
        <span class="text-gray-700">
            <?php echo htmlspecialchars($listing['bedrooms']); ?> Bedrooms
        </span>
    </div>

    <div class="flex items-center">
        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
            <i class="ri-shower-line"></i>
        </div>
        <span class="text-gray-700">
            <?php echo htmlspecialchars($listing['bathrooms']); ?> Bathrooms
        </span>
    </div>

    <div class="flex items-center">
        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
            <i class="ri-building-line"></i>
        </div>
        <span class="text-gray-700">
            <?php echo htmlspecialchars($listing['type']); ?>
        </span>
    </div>
</div>

                    <div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">About this property</h2>
    <p class="text-gray-700 mb-4">
        <?php echo nl2br(htmlspecialchars($listing['description'])); ?>
    </p>
</div>

 <h2 class="text-xl font-semibold text-gray-900 mb-4">Amenities</h2>
<div class="grid grid-cols-2 md:grid-cols-3 gap-4">
    <?php foreach ($amenities as $amenity): ?>
    <div class="flex items-center">
        <div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
            <i class="ri-check-line"></i> <!-- You can replace this icon as needed -->
        </div>
        <span class="text-gray-700"><?php echo htmlspecialchars($amenity); ?></span>
    </div>
    <?php endforeach; ?>
</div>


<!-- rental Conditions -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Rental Conditions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($house_rules as $rule): ?>
            <div class="flex items-start">
                <div class="w-6 h-6 flex items-center justify-center text-primary mr-2 mt-0.5">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <span class="text-gray-700"><?php echo htmlspecialchars($rule); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>


    <div class="mt-8">
  <h2 class="text-2xl font-bold mb-4">Reviews</h2>

  <!-- Display Existing Reviews -->
  <div class="space-y-4">
    <?php if (!empty($reviews)): ?>
      <?php foreach ($reviews as $review): ?>
        <div class="bg-gray-100 p-4 rounded-lg">
          <div class="flex items-center justify-between mb-2">
            <span class="font-semibold"><?= htmlspecialchars($review['username']) ?></span>
            <span class="text-yellow-500">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= round($review['rating'])): ?>
                  ★
                <?php else: ?>
                  ☆
                <?php endif; ?>
              <?php endfor; ?>
            </span>
          </div>
          <p class="text-gray-700"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
          <p class="text-xs text-gray-500 mt-1"><?= date('Y-m-d', strtotime($review['created_at'])) ?></p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-gray-500">No reviews yet.</p>
    <?php endif; ?>
  </div>

  <!-- Leave a Review Button -->
  <?php if (isset($_SESSION['user_id'])): ?>
    <button id="leave-review-btn" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Leave a Review
    </button>

    <!-- Review Form (Initially Hidden) -->
    <form id="review-form" action="submit_review.php" method="post" class="mt-4 bg-white p-4 rounded-lg shadow hidden">
      <input type="hidden" name="listing_id" value="<?= htmlspecialchars($listing_id) ?>">
      <div class="mb-2">
        <label for="rating" class="block font-semibold mb-1">Rating:</label>
        <div id="star-rating" class="flex space-x-1 cursor-pointer text-2xl text-yellow-500">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span data-rating="<?= $i ?>">☆</span>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="rating" id="rating-input" value="0" required>
      </div>
      <div class="mb-2">
        <label for="review_text" class="block font-semibold mb-1">Your Review:</label>
        <textarea name="review_text" id="review_text" rows="4" class="w-full border rounded p-2" required></textarea>
      </div>
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Submit Review
      </button>
    </form>
  <?php else: ?>
    <p class="mt-4 text-gray-600">Please <a href="login.php" class="text-blue-600 hover:underline">log in</a> to leave a review.</p>
  <?php endif; ?>
</div>

<script>
  const leaveReviewBtn = document.getElementById('leave-review-btn');
  const reviewForm = document.getElementById('review-form');
  const stars = document.querySelectorAll('#star-rating span');
  const ratingInput = document.getElementById('rating-input');

  leaveReviewBtn.addEventListener('click', () => {
    reviewForm.classList.toggle('hidden');
  });

  stars.forEach(star => {
    star.addEventListener('click', () => {
      const rating = star.getAttribute('data-rating');
      ratingInput.value = rating;

      stars.forEach(s => {
        s.textContent = s.getAttribute('data-rating') <= rating ? '★' : '☆';
      });
    });
  });
</script>
                
                   
                
                
                
                <!-- Host Information -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-8">
    <div class="flex items-start gap-4">
        <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
            <img src="<?php echo $host_profile_pic; ?>" alt="Host" class="w-full h-full object-cover">
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-semibold text-gray-900 mb-1">Hosted by <?php echo $host_first_name . ' ' . $host_last_name; ?></h2>
            <p class="text-gray-700 mb-4">
                <?php echo ($host_bio); ?>
            </p>      
        </div>
    </div>
</div>
            </div>
     </div>
            
            <!-- Booking Section -->
            <form id="bookingForm" action="process_booking.php" method="POST" onsubmit="return confirmBooking();">
  <div class="lg:col-span-1">
    <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
      <div class="flex items-center justify-between mb-6">
        <span class="text-2xl font-bold text-gray-900">
          $<?php echo $price; ?>
        </span>
        <span class="text-gray-600"> / month</span>

        <!-- Rating and Reviews -->
        <div class="flex items-center">
          <div class="w-5 h-5 flex items-center justify-center text-yellow-400 mr-1">
            <i class="ri-star-fill"></i>
          </div>
          <span class="text-gray-700">
            <?php echo $avg_rating; ?> (<?php echo $review_count; ?> reviews)
          </span>
        </div>
      </div>

      <div class="mb-6">
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">Move-in Date</label>
            <div class="relative">
              <input
                type="date"
                name="move_in"
                class="w-full px-4 py-2 border border-gray-300 rounded-button focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                min="2025-05-30"
                required
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <div class="w-5 h-5 flex items-center justify-center text-gray-500">
                  <i class="ri-calendar-line"></i>
                </div>
              </div>
            </div>
          </div>
          <div>
            <label class="block text-gray-700 text-sm font-medium mb-2">Move-out Date</label>
            <div class="relative">
              <input
                type="date"
                name="move_out"
                class="w-full px-4 py-2 border border-gray-300 rounded-button focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                min="2025-05-30"
                required
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <div class="w-5 h-5 flex items-center justify-center text-gray-500">
                  <i class="ri-calendar-line"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-medium mb-2">Occupants</label>
          <div class="relative">
            <select
              name="occupants"
              class="w-full appearance-none px-4 py-2 pr-8 border border-gray-300 rounded-button focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
              required
            >
              <option value="1">1</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4+</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
              <div class="w-5 h-5 flex items-center justify-center text-gray-500">
                <i class="ri-arrow-down-s-line"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-between font-semibold text-gray-900 pt-4 border-t border-gray-200">
        <span>Rental Price</span>
        <span>$<?php echo $price; ?></span>
      </div>

      <div class="mb-6">
        <label class="relative inline-flex items-center mb-4 cursor-pointer">
          <input
            type="checkbox"
            name="agree_rules"
            value="1"
            class="sr-only peer"
            required
            checked
          />
          <div
            class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"
          ></div>
          <span class="ml-3 text-sm font-medium text-gray-700">I agree to the house rules</span>
        </label>
      </div>
        
    <form id="bookingForm" action="process_booking.php" method="POST" onsubmit="return confirmBooking();">
      <!-- Hidden inputs for IDs and price -->
      <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>" />
      
     <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>" />

      <input type="hidden" name="price" value="<?php echo $price; ?>" />

      <button
        type="submit"
        class="w-full bg-primary text-white py-3 rounded-button hover:bg-blue-600 transition-colors font-medium whitespace-nowrap"
      >
        Book Now
      </button>
    <form>  
      <div class="mt-4 text-center text-sm text-gray-600">You won't be charged yet</div>

      <div class="mt-6 flex items-center justify-center gap-4">
        <div class="w-6 h-6 flex items-center justify-center text-gray-500">
          <i class="ri-shield-check-line"></i>
        </div>
        <span class="text-sm text-gray-600">
          Your booking is protected by Bookit's secure payment system
        </span>
      </div>
    </div>
  </div>
</form>

<script>
  function confirmBooking() {
    return confirm("Are you sure you want to book this property?");
  }
</script>
             
                </div>
            </div>
        </div>
        
    </main>
    

   

      </div>
    </main>

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
              <li><a href="locations.html">Locations</a></li>
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