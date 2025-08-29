<?php
session_start();
require 'db_connect.php'; // PDO connection

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

// Check if profile exists; if not, insert a blank one
$stmt = $conn->prepare("SELECT COUNT(*) FROM profile WHERE id = ?");
$stmt->execute([$user_id]);
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count == 0) {
    $insert = $conn->prepare("INSERT INTO profile (id, first_name, last_name, username, bio, profile_pic) VALUES (?, '', '', '', '', '')");
    $insert->bind_param("i", $user_id);
    $insert->execute();
    $insert->close();
}


// Fetch profile data
$stmt = $conn->prepare("SELECT first_name, last_name, username, bio, profile_pic FROM profile WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

$first_name = $profile['first_name'] ?? '';
$last_name = $profile['last_name'] ?? '';
$username = $profile['username'] ?? '';
$bio = $profile['bio'] ?? '';
$profile_pic = $profile['profile_pic'] ?? 'default-avatar.png';

// Fetch contact details for display and form
$stmt = $conn->prepare("SELECT email, phone, address, country, city, state, zip FROM profile_contact_details WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$contact_details = $result->fetch_assoc() ?? [
    'email' => '', 'phone' => '', 'address' => '', 'country' => '', 'city' => '', 'state' => '', 'zip' => ''
];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $bio = $_POST['bio'] ?? '';

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
            $uploadDir = 'uploads/profile_images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $profile_pic = $destPath;
            } else {
                echo "Error uploading file.";
                exit;
            }
        } else {
            echo "Invalid file type. Allowed: jpg, jpeg, png, gif.";
            exit;
        }
    }

   $update = $conn->prepare("UPDATE profile SET first_name = ?, last_name = ?, username = ?, bio = ?, profile_pic = ? WHERE id = ?");
$update->bind_param("sssssi", $first_name, $last_name, $username, $bio, $profile_pic, $user_id);
$success = $update->execute();
$update->close();


    if ($success) {
        header("Location: profile.php?");
        exit;
    } else {
        echo "Failed to update profile.";
    }
}

$stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$joined_date = $row['created_at']; 
$joined_datetime = new DateTime($joined_date);
$formatted_joined_date = $joined_datetime->format('F Y');  // e.g., 'May 2023'




?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile -Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="flex flex-col min-h-screen">
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

    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-6">
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
                <a href="reviews.php" class="sidebar-link">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                  Reviews
                </a>
                <a href="profile.php" class="sidebar-link active">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  Profile
                </a>
              </nav>
            </div>
          </aside>

          <main class="flex-1 bg-white rounded-lg shadow p-6">
            <div class="space-y-6">
              <div>
                <h1 class="text-2xl font-semibold">Account Settings</h1>
                <p class="text-gray-500">Manage your account information and preferences.</p>
              </div>

              <div class="tabs">
                <div class="tabs-header">
                  <button class="tab-button active" data-tab="profile">Profile</button>
                  <button class="tab-button" data-tab="contact">Contact Details</button>
                </div>



                <form action="profile.php" method="POST" enctype="multipart/form-data">
                  <div class="tab-content active" id="profile-tab">
                    <div class="dashboard-card">
                      <div class="dashboard-card-content pt-6">
                        <div class="space-y-6">
                          <div class="flex flex-col items-center sm:flex-row sm:items-start gap-6">
                            <div class="avatar relative">
                              <img src="<?php echo htmlspecialchars($profile_pic ?? 'default-avatar.png'); ?>" alt="Profile" class="avatar-img">
                              <input type="file" name="profile_pic" class="absolute inset-0 opacity-0 cursor-pointer" title="Change Photo" />
                            </div>
                            <div class="flex flex-col items-center sm:items-start gap-4">
                              <div>
                                <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h3>
                                <p class="text-sm text-gray-500">Joined <?php echo $formatted_joined_date; ?></p>
                              </div>
                              <div>
                                <label class="btn btn-sm btn-outline cursor-pointer">
                                  Change Photo
                                  <input type="file" name="profile_pic" class="hidden" />
                                </label>
                              </div>
                            </div>
                          </div>

                          <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                              <div class="space-y-2">
                                <label for="first_name" class="form-label">First Name</label>
                                <input id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" class="form-input" />
                              </div>

                              <div class="space-y-2">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" class="form-input" />
                              </div>
                            </div>

                            <div class="space-y-2">
                              <label for="username" class="form-label">Username</label>
                              <input id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" class="form-input" />
                            </div>

                            <div class="space-y-2">
                              <label for="bio" class="form-label">Bio</label>
                              <textarea class="form-textarea" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($bio ?? ''); ?></textarea>
                            </div>

                            <div class="pt-4">
                              <button class="btn btn-primary" type="submit">Save Changes</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>

               <form action="contact_details.php" method="POST" >
                <div class="tab-content" id="contact-tab">
                    <div class="dashboard-card">
                      <div class="dashboard-card-content pt-6">
                        <div class="space-y-6">
                          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                              <label for="email" class="form-label">Email Address</label>
                              <input
                                id="email"
                                name="email"
                                type="email"
                                value="<?php echo htmlspecialchars($contact_details['email'] ?? ''); ?>"
                                class="form-input"
                              />
                            </div>

                            <div class="space-y-2">
                              <label for="phone" class="form-label">Phone Number</label>
                              <input
                                id="phone"
                                name="phone"
                                value="<?php echo htmlspecialchars($contact_details['phone'] ?? ''); ?>"
                                class="form-input"
                              />
                            </div>
                          </div>

                          <div class="space-y-2">
                            <label for="address" class="form-label">Address</label>
                            <input
                              id="address"
                              name="address"
                              value="<?php echo htmlspecialchars($contact_details['address'] ?? ''); ?>"
                              class="form-input"
                            />
                          </div>

                          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                              <label for="city" class="form-label">City</label>
                              <input
                                id="city"
                                name="city"
                                value="<?php echo htmlspecialchars($contact_details['city'] ?? ''); ?>"
                                class="form-input"
                              />
                            </div>

                            <div class="space-y-2">
                              <label for="state" class="form-label">State/Province</label>
                              <input
                                id="state"
                                name="state"
                                value="<?php echo htmlspecialchars($contact_details['state'] ?? ''); ?>"
                                class="form-input"
                              />
                            </div>

                            <div class="space-y-2">
                              <label for="zipCode" class="form-label">Zip/Postal Code</label>
                              <input
                                id="zipCode"
                                name="zipCode"
                                value="<?php echo htmlspecialchars($contact_details['zip'] ?? ''); ?>"
                                class="form-input"
                              />
                            </div>
                          </div>

                          <div class="space-y-2">
                            <label for="country" class="form-label">Country</label>
                            <input
                              id="country"
                              name="country"
                              value="<?php echo htmlspecialchars($contact_details['country'] ?? ''); ?>"
                              class="form-input"
                            />
                          </div>

                          <div class="pt-4">
                            <button type="submit" class="btn btn-primary">Update Payment Details</button>
                          </div>


                        </div>
                      </div>
                    </div>
                  </div>
               </form>


              

               
              </div>
            </div>
          </main>
        </div>
      </div>
    </main>

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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tabButtons = document.querySelectorAll('.tab-button');
      const tabContents = document.querySelectorAll('.tab-content');

      tabButtons.forEach(button => {
        button.addEventListener('click', () => {
          // Remove 'active' class from all buttons and contents
          tabButtons.forEach(btn => btn.classList.remove('active'));
          tabContents.forEach(content => content.classList.remove('active'));

          // Add 'active' class to the clicked button
          button.classList.add('active');

          // Get the data-tab attribute to find the corresponding content
          const targetTabId = button.dataset.tab + '-tab'; // e.g., "profile" + "-tab" = "profile-tab"
          const targetTabContent = document.getElementById(targetTabId);

          // Add 'active' class to the target content
          if (targetTabContent) {
            targetTabContent.classList.add('active');
          }
        });
      });

      // Password visibility toggle
      const passwordToggles = document.querySelectorAll('.password-toggle');
      passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
          const passwordInput = this.previousElementSibling;
          if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
          } else {
            passwordInput.type = 'password';
          }
        });
      });

      // Handle profile picture input change
      const profilePicInputs = document.querySelectorAll('input[name="profile_pic"]');
      profilePicInputs.forEach(input => {
        input.addEventListener('change', function(event) {
          const file = event.target.files[0];
          if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
              const avatarImg = document.querySelector('.avatar-img');
              if (avatarImg) {
                avatarImg.src = e.target.result;
              }
            };
            reader.readAsDataURL(file);
          }
        });
      });
    });
  </script>
</body>
</html>