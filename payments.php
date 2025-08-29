<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch payment records for this user (as sender or receiver)
$sql = "
    SELECT 
        pr.id AS payment_id,
        l.title AS property_name,
        u.username AS guest_name,
        pr.created_at AS payment_date,
        pr.amount,
        pr.user_id AS payer_id,
        pr.owner_id AS payee_id,
        pm.method_name,
        pr.listing_id
    FROM payment_records pr
    JOIN listings l ON pr.listing_id = l.id
    JOIN users u ON pr.user_id = u.id
    JOIN payment_methods pm ON pr.payment_method_id = pm.id
    WHERE pr.user_id = ? OR pr.owner_id = ?
    ORDER BY pr.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total income and expenses
$total_income = 0;
$total_expenses = 0;
if ($result && $result->num_rows > 0) {
    // Reset pointer to start
    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        if ($row['payer_id'] == $user_id) {
            $total_expenses += $row['amount'];
        } else {
            $total_income += $row['amount'];
        }
    }
    // Reset pointer again for table display
    $result->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments - Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="flex flex-col min-h-screen">
    <!-- Header/Navbar -->
    <!-- ...existing header code... -->
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
                <a href="payments.php" class="sidebar-link active">
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                  Payment Details
                </a>
                <a href="bookings.php" class="sidebar-link">
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
          
    <main class="flex-grow">
      <div class="container mx-auto px-4 py-8">
        <div class="space-y-6">
          <div>
            <h1 class="text-2xl font-semibold">Payments</h1>
            <p class="text-gray-500">Track and manage your income and expenses from property bookings.</p>
          </div>
          <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-medium">Time frame:</span>
                  <select class="select-filter border rounded-md px-3 py-1">
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                  </select>
                </div>
                
                <a href="add_payment.php" class="btn btn-outline inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <path d="M12 8v8"></path>
                      <path d="M8 10h4a2 2 0 1 1 0 4h-4"></path>
                    </svg>
                    Payment Details
                  </a>

              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg border">
                  <div class="text-sm font-medium text-gray-500 mb-1">Total Income</div>
                  <div class="flex items-center gap-2">
                    <div class="text-2xl font-bold">$<?= number_format($total_income, 2) ?></div>
                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800 ml-2">Incoming</span>
                  </div>
                </div>
                
        
                
                <div class="bg-white p-4 rounded-lg border">
                  <div class="text-sm font-medium text-gray-500 mb-1">Total Expenses</div>
                  <div class="flex items-center gap-2">
                    <div class="text-2xl font-bold">$<?= number_format($total_expenses, 2) ?></div>
                    <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800 ml-2">Outgoing</span>
                  </div>
                </div>
              </div>

          <div class="border rounded-md overflow-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php if ($result && $result->num_rows > 0): ?>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td class="px-6 py-4 whitespace-nowrap font-medium"><?= htmlspecialchars($row['property_name']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['guest_name']) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap"><?= date('Y-m-d', strtotime($row['payment_date'])) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($row['amount'], 2) ?></td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($row['payer_id'] == $user_id): ?>
                          <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Outgoing</span>
                        <?php else: ?>
                          <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Incoming</span>
                        <?php endif; ?>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right">
                        <?= htmlspecialchars(ucfirst($row['method_name'])) ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="px-4 py-2 text-center">No payment records found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
    <!-- ...existing footer code... -->
  </div>
</body>
</html>
