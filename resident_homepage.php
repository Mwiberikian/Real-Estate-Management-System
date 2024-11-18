<?php
// Start session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug: Check session
if (!isset($_SESSION['tenant_id'])) {
    die("Error: Tenant not logged in. Redirecting to login page...");
    header("Location: login.php");
    exit();
}

// Database connection
$host = 'localhost:3307';
$db = 'software';
$user = 'root';
$pass = 'oliviamumbi2010';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// Fetch maintenance requests for a resident
if (isset($_GET['fetch_requests'])) {
    $residentId = 1; // Replace with the logged-in resident's ID (from session)
    $requests = [];
    $sql = "SELECT id, property_number, description, priority, status, created_at, image_path 
            FROM maintenance_requests 
            WHERE resident_id = ? 
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $residentId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    echo json_encode($requests);
    exit;
}


// Ensure image directory exists
$imageDir = "resident_problem_images";
if (!is_dir($imageDir)) {
    mkdir($imageDir, 0777, true);
}

// Fetch property_id if not in session
if (!isset($_SESSION['property_id'])) {
    $tenantId = $_SESSION['tenant_id'];
    $stmt = $conn->prepare("SELECT property_id FROM tenants WHERE tenant_id = ?");
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $stmt->bind_result($propertyId);
    if ($stmt->fetch()) {
        $_SESSION['property_id'] = $propertyId;
    } else {
        die("Error: Unable to fetch property ID.");
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_maintenance'])) {
    $tenantId = $_SESSION['tenant_id'];
    $propertyId = $_SESSION['property_id'];
    $description = $conn->real_escape_string($_POST['requestDescription']);
    $priority = $conn->real_escape_string($_POST['requestPriority']);

    // Validate form inputs
    if (empty($description) || empty($priority)) {
        die("Error: Description and priority are required.");
    }

    $imagePath = null;
    if (isset($_FILES['problemImage']) && $_FILES['problemImage']['error'] == UPLOAD_ERR_OK) {
        $fileName = uniqid() . "_" . basename($_FILES['problemImage']['name']);
        $imagePath = $imageDir . "/" . $fileName;
        if (!move_uploaded_file($_FILES['problemImage']['tmp_name'], $imagePath)) {
            die("Error uploading image.");
        }
    }

    // Insert maintenance request
    $stmt = $conn->prepare(
        "INSERT INTO maintenance_requests (tenant_id, property_id, description, priority, image_path) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iisss", $tenantId, $propertyId, $description, $priority, $imagePath);

    if ($stmt->execute()) {
        echo "Maintenance request submitted successfully!";
    } else {
        die("Error: " . $stmt->error);
    }
    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="resident.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="landingpageimages/image-removebg-preview.png" alt="logo" width="160" height="130">
        </div>
        <nav class="nav">
            <a href="#payments">PAYMENTS</a>
            <a href="#maintenance">MAINTENANCE</a>
            <a href="#guests">GUESTS</a>
            <a href="index.html" onclick="logout()">LOGOUT</a>
        </nav>
    </header>

    <main>
        <aside class="sidebar">
            <section id="edit-profile">
                <h2>Your Profile</h2>
                <p>View and edit your profile details here.</p>
                <button onclick="openModal('editProfileModal')">View Here</button>
            </section>

            <section id="rental-payments">
                <h2>Rental Payments</h2>
                <p>Make your online rent payments here.</p>
                <button onclick="window.open('payment.html', '_blank')">Make Payment</button>
            </section>

            <section id="maintenance-requests">
                <h2>Maintenance Requests</h2>
                <p>Submit maintenance requests and track their status.</p>
                <button onclick="openModal('submitRequestModal')">Submit Request</button>
            </section>

            <section id="notifications">
                <h2>Notifications</h2>
                <p>Rent payment due date REMINDER!!!</p>
                <button onclick="openModal('settingsModal')">Settings</button>
            </section>
        </aside>

        <section class="content">
            <div>
                <h3>Rental Payment History</h3>
                <canvas id="rentalChart"></canvas>
            </div>
        </section>
    </main>

    <!-- Modals -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editProfileModal')">&times;</span>
            <h2>Profile Information</h2>
            <p>Name: <span id="residentNameProfile">Resident1</span></p>
            <p>House Number: <span id="houseNumber">1234</span></p>
            <button onclick="closeModal('editProfileModal')">Close</button>
        </div>
    </div>

    <div id="submitRequestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('submitRequestModal')">&times;</span>
            <h2>Raise A Ticket</h2>
            <form method="POST" action="resident_homepage.php" enctype="multipart/form-data">
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
        </div>
    </div>

    <!-- Script -->
    <script>
        const rentalData = [1200, 1500, 1100, 1400, 1350];
        const rentalLabels = ['January', 'February', 'March', 'April', 'May'];

        const rentalCtx = document.getElementById('rentalChart').getContext('2d');
        const rentalChart = new Chart(rentalCtx, {
            type: 'bar',
            data: {
                labels: rentalLabels,
                datasets: [{
                    label: 'Payments (KSh)',
                    data: rentalData,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function logout() {
            window.location.href = "index.html";
        }
    </script>
</body>
</html>

