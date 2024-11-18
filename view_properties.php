<?php
include('db_connect.php');

// Get all properties from the database
$sql = "SELECT p.property_id, p.house_number, p.price_per_month, p.bedrooms, pi.image_path 
        FROM Properties p
        LEFT JOIN Property_Images pi ON p.property_id = pi.property_id 
        GROUP BY p.property_id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Properties</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="propertydetails.css"> <!-- Add your CSS file here -->
</head>
<body>
    <h1>Properties Available for Rent</h1>

    <!-- Property Listings -->
    <div class="property-listings">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="property-card" onclick="window.location.href='property_details.php?id=<?php echo $row['property_id']; ?>'">
                <!-- Display property image -->
                <div class="property-image-container">
                    <img src="<?php echo $row['image_path']; ?>" alt="Property Image" class="property-image">
                </div>

                <!-- Property details -->
                <h3><?php echo $row['house_number']; ?></h3>
                <p><?php echo $row['bedrooms']; ?> Bedrooms | KSh <?php echo number_format($row['price_per_month']); ?> / month</p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
