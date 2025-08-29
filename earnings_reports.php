<?php
include 'db_connect.php';
session_start();

// 1. Monthly Platform Revenue (5% commission from every payment)
$monthlyRevenue = [];
$sql = "SELECT YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total_amount, SUM(amount)*0.05 as platform_earnings
        FROM payment_records
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY year DESC, month DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $monthlyRevenue[] = $row;
}

// 2. Top Earning Hosts (total received, top 5)
$topHosts = [];
$sql = "SELECT u.id, u.username, SUM(pr.amount) as total_received, SUM(pr.amount)*0.05 as platform_cut
        FROM payment_records pr
        JOIN users u ON pr.owner_id = u.id
        GROUP BY pr.owner_id
        ORDER BY total_received DESC
        LIMIT 5";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $topHosts[] = $row;
}

// 3. Top Paying Tenants (total paid, top 5)
$topTenants = [];
$sql = "SELECT u.id, u.username, SUM(pr.amount) as total_paid, SUM(pr.amount)*0.05 as platform_cut
        FROM payment_records pr
        JOIN users u ON pr.user_id = u.id
        GROUP BY pr.user_id
        ORDER BY total_paid DESC
        LIMIT 5";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $topTenants[] = $row;
}

// 4. Revenue by Property (top 5 properties)
$topProperties = [];
$sql = "SELECT l.id, l.title, SUM(pr.amount) as total_earned, SUM(pr.amount)*0.05 as platform_cut
        FROM payment_records pr
        JOIN listings l ON pr.listing_id = l.id
        GROUP BY pr.listing_id
        ORDER BY total_earned DESC
        LIMIT 5";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $topProperties[] = $row;
}

// 5. Platform Total Revenue (all time)
$sql = "SELECT SUM(amount)*0.05 as total_platform_revenue FROM payment_records";
$res = $conn->query($sql);
$platformTotal = $res->fetch_assoc();

// Helper for month name
function monthName($month) {
    return date('F', mktime(0,0,0,$month,1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Earnings Reports</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f7f9fb; color: #222; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px #0001; padding: 32px 40px; }
        h1 { color: #0077cc; margin-bottom: 0.5em; }
        h2 { color: #333; margin-top: 2.5em; margin-bottom: 0.5em; }
        .summary { font-size: 1.3em; margin-bottom: 2em; background: #eaf6ff; border-left: 6px solid #0077cc; padding: 1em 1.5em; border-radius: 6px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 2em; background: #fafcff; border-radius: 8px; overflow: hidden; }
        th, td { border: 1px solid #e3e8ee; padding: 12px 10px; text-align: left; }
        th { background: #0077cc; color: #fff; font-weight: 600; }
        tr:nth-child(even) { background: #f4f8fb; }
        tr:hover { background: #eaf6ff; }
        .highlight { color: #0077cc; font-weight: bold; }
        .section-desc { color: #666; font-size: 1em; margin-bottom: 1em; }
        @media (max-width: 700px) {
            .container { padding: 10px; }
            th, td { font-size: 0.95em; padding: 8px 4px; }
        }
    </style>
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
<div class="container">
<h1>📊 Platform Earnings & Revenue Report</h1>
<div class="summary">
    <b>Total Platform Revenue (All Time):</b> <span class="highlight">$<?= number_format($platformTotal['total_platform_revenue'], 2) ?></span><br>
    <span style="font-size:0.95em; color:#555;">(5% commission from all completed transactions)</span>
</div>

<h2>Monthly Platform Revenue</h2>
<div class="section-desc">See how the platform's earnings and total payments have changed over time.</div>
<table>
    <tr><th>Year</th><th>Month</th><th>Total Payments</th><th>Platform Earnings (5%)</th></tr>
    <?php foreach ($monthlyRevenue as $row): ?>
        <tr>
            <td><?= $row['year'] ?></td>
            <td><?= monthName($row['month']) ?></td>
            <td>$<?= number_format($row['total_amount'], 2) ?></td>
            <td class="highlight">$<?= number_format($row['platform_earnings'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Top Earning Hosts</h2>
<div class="section-desc">Hosts who have received the most payments through the platform.</div>
<table>
    <tr><th>Host</th><th>Total Received</th><th>Platform Cut (5%)</th></tr>
    <?php foreach ($topHosts as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td>$<?= number_format($row['total_received'], 2) ?></td>
            <td class="highlight">$<?= number_format($row['platform_cut'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Top Paying Tenants</h2>
<div class="section-desc">Tenants who have paid the most through the platform.</div>
<table>
    <tr><th>Tenant</th><th>Total Paid</th><th>Platform Cut (5%)</th></tr>
    <?php foreach ($topTenants as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td>$<?= number_format($row['total_paid'], 2) ?></td>
            <td class="highlight">$<?= number_format($row['platform_cut'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Top Revenue Properties</h2>
<div class="section-desc">Properties that have generated the most revenue.</div>
<table>
    <tr><th>Property</th><th>Total Earned</th><th>Platform Cut (5%)</th></tr>
    <?php foreach ($topProperties as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td>$<?= number_format($row['total_earned'], 2) ?></td>
            <td class="highlight">$<?= number_format($row['platform_cut'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
</body>
</html>
