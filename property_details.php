<?php
include('db_connect.php');

// Retrieve property details from the database
$propertyId = $_GET['id'];
$sql = "SELECT p.property_id, p.house_number, p.price_per_month, p.bedrooms, p.description, pi.image_path 
        FROM Properties p
        LEFT JOIN Property_Images pi ON p.property_id = pi.property_id
        WHERE p.property_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>
    <link rel="stylesheet" href="bookings.css">
</head>
<body>
    <header class="header">
        <h1>Rosewood Park Estate</h1>
        <p>Find your perfect home</p>
    </header>

    <div class="property-details-container">
        <!-- Property Details Card -->
        <div class="property-details">
            <div class="property-image-container">
                <img src="<?php echo $property['image_path']; ?>" alt="Property Image" class="property-image">
            </div>

            <h2>House: <?php echo $property['house_number']; ?></h2>
            <p><strong>Bedrooms:</strong> <?php echo $property['bedrooms']; ?></p>
            <p><strong>Price:</strong> KSh <?php echo number_format($property['price_per_month']); ?> / month</p>
            <p><strong>Description:</strong> <?php echo $property['description']; ?></p>
        </div>

        <!-- Booking Form Card -->
        <div class="booking-form">
            <h3>Book This Property</h3>
            <form method="POST" action="book_property.php">
                <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">

                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required>

                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required>

                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email address" required>

                <label for="phone_number">Phone Number</label>
                <input type="tel" name="phone_number" id="phone_number" placeholder="Enter your phone number" required>

                <button type="submit" class="btn"><span>Book Now</span></button>
            </form>

            <!-- Contact Options -->
            <div class="contact-options">
                <p>Need to contact the owner?</p>
                <a href="https://wa.me/+254700123456" class="btn whatsapp-btn" target="_blank"><span>Contact via WhatsApp</span></a>
                <a href="tel:+254700123456" class="btn phone-btn"><span>Call the Owner</span></a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Rosewood Park Estate. All rights reserved.</p>
    </footer>
</body>
</html>
