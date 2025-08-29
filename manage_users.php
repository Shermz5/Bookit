<?php
// Database connection established
include 'db_connect.php'; 
session_start(); // make sure session is started
// Check if the user is an admin

// Fetch all users
$userQuery = $conn->prepare("SELECT id, first_name, last_name, email FROM users");
$userQuery->execute();
$usersResult = $userQuery->get_result();
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM suspended_users";
$result = $conn->query($sql);
$suspended_users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suspended_users[] = $row;
    }
}
// Fetch user queries
$queryQuery = $conn->prepare("SELECT id, username, email, subject, message, submitted_at FROM queries");
$queryQuery->execute();
$queryResult = $queryQuery->get_result();
$queries = $queryResult->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
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
            <a href="user_management.php" class="nav-link active">User  Management</a>
            <a href="logout.php" class="nav-link">Logout</a>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-semibold">User  Management</h1>
        
        <!-- User List -->
        <div class="mt-6">
          <h2 class="text-lg font-semibold">Registered Users</h2>
         <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
  <thead class="bg-gray-100 border-b">
    <tr>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">First Name</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Last Name</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Email</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['first_name']); ?></td>
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['last_name']); ?></td>
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['email']); ?></td>
        <td class="px-6 py-4 text-sm">
          <a href="suspend_user.php?id=<?php echo $user['id']; ?>" class="text-red-600 hover:underline">Suspend</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2 class="text-lg font-semibold mb-4">Suspended Users</h2>

<table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
  <thead class="bg-gray-100 border-b">
    <tr>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">First Name</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Last Name</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Email</th>
      <th class="text-left px-6 py-3 text-sm font-medium text-gray-700">Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($suspended_users as $user): ?>
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['first_name']); ?></td>
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['last_name']); ?></td>
        <td class="px-6 py-4 text-sm text-gray-800"><?php echo htmlspecialchars($user['email']); ?></td>
        <td class="px-6 py-4 text-sm">
          <a href="reactivate_user.php?id=<?php echo $user['id']; ?>" class="text-green-600 hover:underline">Reactivate</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


        </div>

        <!-- User Queries -->
        <div class="mt-6">
          <h2 class="text-lg font-semibold">User  Queries</h2>
          <table class="min-w-full bg-white">
            <thead>
              <tr>
                <th class="py-2">Username</th>
                <th class="py-2">Email</th>
                <th class="py-2">Subject</th>
                <th class="py-2">Message</th>
                <th class="py-2">Submitted At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($queries as $query): ?>
                <tr>
                  <td class="py-2"><?php echo htmlspecialchars($query['username']); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars($query['email']); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars($query['subject']); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars($query['message']); ?></td>
                  <td class="py-2"><?php echo htmlspecialchars($query['submitted_at']); ?></td>
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

