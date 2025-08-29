<?php
// edit_listing.php

include 'db_connect.php';

// Check if 'id' is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $listing_id = intval($_GET['id']);

    // Fetch the listing from the database
    $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $listing = $result->fetch_assoc();

        // Parse amenities and rental_conditions into arrays
        $amenities = explode(',', $listing['amenities']);
        $rental_conditions = explode(',', $listing['rental_conditions']);
    } else {
        echo "Listing not found.";
        exit;
    }

    $stmt->close();
} else {
    echo "Invalid listing ID.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Property - Bookit</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Use your header here -->
  <main>
    <section class="submit-property">
      <div class="container">
        <div class="section-header">
          <h1>Edit Your Property</h1>
          <p>Update your property details on Bookit.</p>
        </div>
        <div class="submit-form-container">
          <form id="propertyEditForm" class="submit-form" method="POST" action="update_listing.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($listing['id']) ?>">

            <!-- BASIC INFORMATION -->
            <div class="form-section">
              <h2 class="form-section-title">Basic Information</h2>
              <div class="form-group">
                <label for="propertyTitle">Property Title*</label>
                <input type="text" id="propertyTitle" name="title" value="<?= htmlspecialchars($listing['title']) ?>" required>
              </div>

              <div class="form-group">
                <label for="propertyType">Property Type*</label>
                <select id="propertyType" name="type" required>
                  <?php
                  $types = ['apartment', 'house', 'villa', 'cabin', 'cottage', 'loft', 'bungalow'];
                  foreach ($types as $type) {
                      $selected = ($listing['type'] === $type) ? 'selected' : '';
                      echo "<option value=\"$type\" $selected>".ucfirst($type)."</option>";
                  }
                  ?>
                </select>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="propertyPrice">Price per month($)*</label>
                  <input type="number" id="propertyPrice" name="price" value="<?= htmlspecialchars($listing['price']) ?>" min="1" required>
                </div>
                <div class="form-group">
                  <label for="propertyBedrooms">Bedrooms*</label>
                  <input type="number" id="propertyBedrooms" name="bedrooms" value="<?= htmlspecialchars($listing['bedrooms']) ?>" min="1" max="20" required>
                </div>
                <div class="form-group">
                  <label for="propertyBathrooms">Bathrooms*</label>
                  <input type="number" id="propertyBathrooms" name="bathrooms" value="<?= htmlspecialchars($listing['bathrooms']) ?>" min="1" max="20" required>
                </div>
              </div>
            </div>

            <!-- LOCATION DETAILS -->
            <div class="form-section">
              <h2 class="form-section-title">Location Details</h2>
              <div class="form-group">
                <label for="propertyAddress">Address*</label>
                <input type="text" id="propertyAddress" name="address" value="<?= htmlspecialchars($listing['address']) ?>" required>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="propertyCity">City*</label>
                  <input type="text" id="propertyCity" name="city" value="<?= htmlspecialchars($listing['city']) ?>" required>
                </div>
                <div class="form-group">
                  <label for="propertyState">State/Province*</label>
                  <input type="text" id="propertyState" name="state" value="<?= htmlspecialchars($listing['state']) ?>" required>
                </div>
                <div class="form-group">
                  <label for="propertyZip">Postal Code*</label>
                  <input type="text" id="propertyZip" name="zip" value="<?= htmlspecialchars($listing['zip']) ?>" required>
                </div>
                <div class="form-group">
                  <label for="propertyCountry">Country*</label>
                  <input type="text" id="propertyCountry" name="country" value="<?= htmlspecialchars($listing['country']) ?>" required>
                </div>
              </div>
            </div>

            <!-- PROPERTY DETAILS -->
            <div class="form-section">
              <h2 class="form-section-title">Property Details</h2>
              <div class="form-group">
                <label for="propertyDescription">Description*</label>
                <textarea id="propertyDescription" name="description" rows="5" required><?= htmlspecialchars($listing['description']) ?></textarea>
              </div>
              <div class="form-group">
                <label>Amenities</label>
                <div class="checkbox-grid">
                  <?php
                  $amenitiesList = [
                      'wifi' => 'Wi-Fi',
                      'solar' => 'Electricity/Solar',
                      'kitchen' => 'Kitchen',
                      'backyard' => 'Backyard/Workspace',
                      'parking' => 'Parking',
                      'pool' => 'Pool',
                      'water' => 'Backup Water Supply',
                      'durawall' => 'Durawall'
                  ];
                  foreach ($amenitiesList as $value => $label) {
                      $checked = in_array($value, $amenities) ? 'checked' : '';
                      echo "<div class=\"checkbox-item\">
                              <input type=\"checkbox\" id=\"amenity_$value\" name=\"amenities[]\" value=\"$value\" $checked>
                              <label for=\"amenity_$value\">$label</label>
                            </div>";
                  }
                  ?>
                </div>
              </div>
            </div>

            <!-- RENTAL CONDITIONS -->
            <div class="form-group">
              <label>Rental Conditions</label>
              <div class="checkbox-grid">
                <?php
                $conditionsList = [
                    'pets_allowed' => 'Pets Allowed',
                    'no_pets' => 'No Pets',
                    'short_term' => 'Short Term',
                    'long_term' => 'Long Term',
                    'deposit_required' => 'Deposit Required',
                    'students' => 'Students',
                    'couples' => 'Couples',
                    'singles' => 'Singles',
                    'bills_included' => 'Bills Included',
                    'bills_excluded' => 'Bills Excluded'
                ];
                foreach ($conditionsList as $value => $label) {
                    $checked = in_array($value, $rental_conditions) ? 'checked' : '';
                    echo "<div class=\"checkbox-item\">
                            <input type=\"checkbox\" id=\"condition_$value\" name=\"rental_conditions[]\" value=\"$value\" $checked>
                            <label for=\"condition_$value\">$label</label>
                          </div>";
                }
                ?>
              </div>
            </div>

            <!-- IMAGES (Optional) -->
            <div class="form-section">
              <h2 class="form-section-title">Images</h2>
              <div class="form-group">
                <label for="propertyImages">Upload Images (Max 5)</label>
                <input type="file" name="images[]" id="propertyImages" accept="image/*" multiple>
              </div>
            </div>

            <!-- ACTIONS -->
            <div class="form-actions">
              <button type="reset" class="btn btn-outline">Reset Form</button>
              <button type="submit" class="btn btn-primary">Update Property</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
  <!-- Use your footer here -->
</body>
</html>
