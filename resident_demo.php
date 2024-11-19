<?php
// Database connection
$host = 'localhost:3307';
$db = 'software';
$user = 'root';
$pass = 'oliviamumbi2010'; // Update password
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the directory for images exists
$imageDir = "resident_problem_images";
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// Handle form submission for maintenance request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_maintenance'])) {
    $property_number = $conn->real_escape_string($_POST['propertyNumber']);
    $description = $conn->real_escape_string($_POST['requestDescription']);
    $priority = $conn->real_escape_string($_POST['requestPriority']);
    $resident_id = 1; // Example resident ID

    $imagePath = null; // Default image path is null
    if (isset($_FILES['problemImage']) && $_FILES['problemImage']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['problemImage']['name']);
        $imagePath = $imageDir . '/' . uniqid() . "_" . $fileName; // Generate unique file name
        move_uploaded_file($_FILES['problemImage']['tmp_name'], $imagePath);
    }

    $stmt = $conn->prepare("INSERT INTO maintenance_requests (resident_id, property_number, description, priority, image_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $resident_id, $property_number, $description, $priority, $imagePath);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Demo</title>
    <link rel="stylesheet" href="resident_demo.css">
</head>
<body>
<div class="container">
    <header>
        <h1>Resident Portal</h1>
    </header>

    <main>
        <section>
            <h2>Submit Maintenance Request</h2>
            <form method="POST" action="resident_demo.php" enctype="multipart/form-data">
                <label for="propertyNumber">Property Number:</label>
                <input type="text" id="propertyNumber" name="propertyNumber" required>

                <label for="requestDescription">Description:</label>
                <textarea id="requestDescription" name="requestDescription" required></textarea>

                <label for="requestPriority">Priority:</label>
                <select id="requestPriority" name="requestPriority" required>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>

                <label for="problemImage">Upload Image:</label>
                <input type="file" id="problemImage" name="problemImage" accept="image/*">

                <button type="submit" name="submit_maintenance">Submit Request</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
