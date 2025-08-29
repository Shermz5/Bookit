<?php
include 'db_connect.php'; // assumes you have this connection script

// Fetch featured properties
$sql = "SELECT id, title, location, price, images FROM listings WHERE is_featured = 1 LIMIT 6";
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
        <button class="btn btn-circle" id="prevProperty">
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
          <div class="property-card">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Property Image">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= htmlspecialchars($row['location']) ?></p>
            <p class="price">$<?= number_format($row['price']) ?></p>
            <a href="explore.php?id=<?= $row['id'] ?>" class="btn btn-primary">View Details</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No featured properties available right now.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
