<?php
session_start();

// Include your DB connection
include 'db_connect.php';

// Check if the user is an admin
// (You may want to add your admin check logic here)

// Fetch properties
$propertyQuery = $conn->prepare("
    SELECT listings.id, listings.title, listings.description, listings.price, 
           users.first_name, users.last_name, users.username 
    FROM listings 
    JOIN users ON listings.host_id = users.id
");
$propertyQuery->execute();
$propertyResult = $propertyQuery->get_result();
$properties = $propertyResult->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Properties Management - Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
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
            <a href="admin_dashboard.php">
              <span class="logo-text">Admin Panel</span>
            </a>
          </div>
          <nav class="desktop-nav">
            <a href="properties_management.php" class="nav-link active">Properties Management</a>
            <a href="logout.php" class="nav-link">Logout</a>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold">Properties Management</h1>
        
        <!-- Properties List -->
        <div class="mt-6">
          <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-gray-100 border-b">
              <tr>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Property Title</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Description</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Price</th>
                <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Host Name</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($properties as $property): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($property['title']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($property['description']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800">$<?php echo htmlspecialchars($property['price']); ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($property['first_name'] . ' ' . $property['last_name']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container">
        <p>© 2025 Bookit. All rights reserved.</p>
      </div>
    </footer>
  </div>
</body>
</html>
