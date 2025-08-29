<?php
session_start();

// Include your DB connection
include 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and assign inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO queries (username, email, subject, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $email, $subject, $message);

    // Execute and check
    if ($stmt->execute()) {
        echo "<script>alert('Thank you for your input will consider it.'); window.location.href='about.php';</script>";
    } else {
        echo "<script>alert('Error: Could not send message.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - Bookit</title>
  <link rel="stylesheet" href="about.css">
  <link rel="stylesheet" href="style.css">
  

  <style>
      .profile-dropdown {
        position: relative;
        display: inline-block;
      }

      .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.2s;
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
            <a href="home.php" >Home</a>
            <a href="explore.php">Explore</a>
            <a href="about.php" class="active">About</a>
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

  <div class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
      <div class="about-hero-content">
        <h1 class="about-hero-title fade-in">We're Changing How You Book Your Stays</h1>
        <p class="about-hero-subtitle fade-in">A community-focused platform connecting tenants with local property  owners for authentic spaces</p>
      </div>
    </section>

    <!-- Our Mission Section -->
    <section class="mission-section">
      <div class="container">
        <div class="mission-content">
          <div class="mission-text">
            <h2 class="section-heading">Our Mission</h2>
            <p class="mission-description">
              At Bookit, we believe travel should be personal, affordable, and community-centered. We're building a platform that connects tenants directly with local property owners, cutting out the middlemen and creating authentic experiences that benefit both visitors and neighborhoods.
            </p>
            <p class="mission-description">
              Unlike large corporate platforms, we focus on fair pricing, transparency, and fostering real connections between landlords and their tenants(guests). We're committed to sustainable booking that respects local communities and environments.
            </p>
          </div>
          <div class="mission-image-container">
            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267" alt="A cozy home interior" class="mission-image">
          </div>
        </div>
      </div>
    </section>

    <!-- Core Values Section -->
    <section class="values-section">
      <div class="container">
        <h2 class="section-heading text-center">Our Core Values</h2>
        <div class="values-grid">
          <div class="value-card fade-in">
            <div class="value-icon-container">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="value-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="value-title">Community First</h3>
            <p class="value-description">
              We build connections between tenants and landlord(property owners) that benefit local neighborhoods and create authentic experiences.
            </p>
          </div>

          <div class="value-card fade-in">
            <div class="value-icon-container">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="value-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="value-title">Fair Pricing</h3>
            <p class="value-description">
              We keep our fees low and transparent, ensuring both owners and tenants get more value from every booking.
            </p>
          </div>

          <div class="value-card fade-in">
            <div class="value-icon-container">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="value-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="value-title">Sustainability</h3>
            <p class="value-description">
              We promote friendly accommodations and responsible practices that respect local environments.
            </p>
          </div>

          <div class="value-card fade-in">
            <div class="value-icon-container">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="value-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="value-title">Trust & Safety</h3>
            <p class="value-description">
              We prioritize the security of our community with verified profiles, secure payments, and responsive support.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Our Story Section -->
    <section class="story-section">
      <div class="container">
        <div class="story-content">
          <div class="story-image-container">
            <img src="https://images.unsplash.com/photo-1583321500900-82807e458f3c" alt="Team working together" class="story-image">
          </div>
          <div class="story-text">
            <h2 class="section-heading">Our Story</h2>
            <p class="story-description">
              Bookit was founded in 2025 by a group of students frustrated with the high demand workload for looking for accommodation and impersonal experiences of mainstream booking platforms. We started with a simple idea: create a platform that connects tenants directly with local landlords while keeping costs low and experiences authentic.
            </p>
            <p class="story-description">
              What began as a small network in Bindura quickly expanded as tenants and lanlords embraced our community-first approach. Today, we're proud to connect tenants with unique requirements across the country, while staying true to our founding principles.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
      <div class="container">
        <h2 class="section-heading text-center">Meet Our Team</h2>
        <div class="team-grid">
          <div class="team-member fade-in">
            <div class="team-photo-container">
              <img src="Shermza.png" alt="Sherman" class="team-photo">
            </div>
            <h3 class="team-name">Sherman Mehlo</h3>
            <p class="team-role">Member</p>
            <p class="team-bio">Computer Science Student 2.2</p>
          </div>

          <div class="team-member fade-in">
            <div class="team-photo-container">
              <img src="Amunike.jpg" alt="Amunike" class="team-photo">
            </div>
            <h3 class="team-name">Amunike Sibanibani</h3>
            <p class="team-role">Member</p>
            <p class="team-bio">Computer Science Student 2.2</p>
          </div>

          <div class="team-member fade-in">
            <div class="team-photo-container">
              <img src="Arthur.jpg" alt="Arthur" class="team-photo">
            </div>
            <h3 class="team-name">Arthur Chikondo</h3>
            <p class="team-role">Member</p>
            <p class="team-bio">Computer Science Student 2.1</p>
          </div>

          <div class="team-member fade-in">
            <div class="team-photo-container">
              <img src="Primrose.jpg" alt="Primrose" class="team-photo">
            </div>
            <h3 class="team-name">Primrose Mhike</h3>
            <p class="team-role">Member</p>
            <p class="team-bio">Computer Science Student 2.2</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
      <div class="container">
        <div class="contact-grid">
          <div class="contact-info">
            <h2 class="section-heading">Get in Touch</h2>
            <p class="contact-description">
              Have questions or feedback? We'd love to hear from you. Reach out to our team using any of the methods below.
            </p>
            
            <div class="contact-methods">
              <div class="contact-method">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="contact-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <div>
                  <h3 class="contact-method-title">Email</h3>
                  <p class="contact-method-value">hello@bookit.com</p>
                </div>
              </div>
              
              <div class="contact-method">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="contact-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <div>
                  <h3 class="contact-method-title">Phone</h3>
                  <p class="contact-method-value">+263 773467351</p>
                </div>
              </div>
              
              <div class="contact-method">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="contact-icon">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                  <h3 class="contact-method-title">Address</h3>
                  <p class="contact-method-value">Bindura University of Science Education</p>
                </div>
              </div>
            </div>

            <div class="social-links">
              <a href="#" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                </svg>
              </a>
              <a href="#" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
              </a>
              <a href="#" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                </svg>
              </a>
              <a href="#" class="social-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                </svg>
              </a>
            </div>
          </div>
          
          <div class="contact-form-container">
            <form class="contact-form" action="about.php" method="POST">
  <div class="form-group">
    <label for="name">Username</label>
    <input type="text" id="username" name="username" required>
  </div>
  
  <div class="form-group">
    <label for="email">Your Email</label>
    <input type="email" id="email" name="email" required>
  </div>
  
  <div class="form-group">
    <label for="subject">Subject</label>
    <input type="text" id="subject" name="subject" required>
  </div>
  
  <div class="form-group">
    <label for="message">Message</label>
    <textarea id="message" name="message" rows="4" required></textarea>
  </div>
  
  <button type="submit" class="submit-button">Send Message</button>
</form>

          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div>
            <h3 class="footer-heading">Bookit</h3>
            <ul class="footer-links">
              <li><a href="#">About Us</a></li>
              <li><a href="#">How it Works</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Press</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="footer-heading">Community</h3>
            <ul class="footer-links">
              <li><a href="#">Local Hosts</a></li>
              <li><a href="#">Referrals</a></li>
              <li><a href="#">Reviews</a></li>
              <li><a href="#">Events</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="footer-heading">Hosts</h3>
            <ul class="footer-links">
              <li><a href="#">Become a Host</a></li>
              <li><a href="#">Responsible Hosting</a></li>
              <li><a href="#">Host Resources</a></li>
              <li><a href="#">Community Forum</a></li>
            </ul>
          </div>
          
          <div>
            <h3 class="footer-heading">Support</h3>
            <ul class="footer-links">
              <li><a href="#">Help Center</a></li>
              <li><a href="#">Trust & Safety</a></li>
              <li><a href="#">Cancellation Options</a></li>
              <li><a href="#">Contact Us</a></li>
            </ul>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; <span id="current-year"></span> Bookit. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</body>
</html>