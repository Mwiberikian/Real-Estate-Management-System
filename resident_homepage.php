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
$host = 'localhost';
$db = 'software';
$user = 'root';
$pass = '';
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
    <style>
        /* Hide all sections by default */
        .content div {
            display: none;
        }

        /* Button styling */
        .button-link {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 15px;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .button-link:hover {
            background-color: #45a049;
        }

        /* Additional styling */
        .profile-container {
            display: flex;
        }

        .profile-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }
        
        .profile-details p {
            margin: 5px 0;
        }

        .maintenance-form label, .maintenance-form input, .maintenance-form select, .maintenance-form textarea {
            display: block;
            margin: 10px 0;
        }

        /* Style for switches (notifications) */
        .switch {
            position: relative;
            display: inline-block;
            width: 34px;
            height: 20px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 12px;
            width: 12px;
            border-radius: 50%;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
        }

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:checked + .slider:before {
            transform: translateX(14px);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="landingpageimages/image-removebg-preview.png" alt="logo" width="160" height="130">
        </div>
        <nav class="nav">
            <a href="#home" style="--1" onclick="showBarChart()">HOME</a>
            <a href="payment.html" style="--1">PAYMENTS</a>
            <a href="index.html" style="--1" onclick="logout()">LOGOUT</a>
        </nav>
        
    </header>

    <main>
        <aside class="sidebar">
            <section id="edit-profile">
                <h2>Your Profile</h2>
                <p>View and edit your profile details here.</p>
                <button class="button-link" onclick="showContent('profile-section')">View Here</button>
            </section>

            <section id="rental-payments">
                <h2>Rental Payments</h2>
                <p>Make your online rent payments here.</p>
                <a href="payment.html" target="_blank" class="button-link">Make Payment</a>
            </section>

            <section id="maintenance-requests">
                <h2>Maintenance Requests</h2>
                <p>Submit maintenance requests here.</p>
                <button class="button-link" onclick="showContent('maintenance-section')">Submit Request</button>
            </section>

            <section id="notifications">
                <h2>Notifications</h2>
                <p>Change the settings of how you would love to receive your notifications here.</p>
                <button class="button-link" onclick="showContent('notification-settings')">Settings</button>
            </section>
        </aside>

        <section class="content">

            <div id="bar-chart-section">
                <h2>Rental Payments Overview</h2>
                <canvas id="bar-chart"></canvas>
            </div>

            <div id="profile-section" class="profile-section">
                <h2>Welcome, Resident1!</h2>
                <img src="SupportImages/download (3).jpeg" alt="Resident Photo" onerror="this.src='default-avatar.png';" class="profile-photo">
                <p><strong>Name:</strong> Resident1</p>
                <p><strong>House Number:</strong> 1234</p>
                <p><strong>Contact:</strong> +254 712-345-678</p>
                <p><strong>Email:</strong> resident1@example.com</p>
                <p><strong>Member Since:</strong> January 2023</p>
                <div class="profile-actions">
                    <button onclick="editProfile()">Edit Profile</button>
                    <button onclick="viewDocuments()">View Documents</button>
                    <button onclick="logout()">Log Out</button>
                </div>
            </div>
            

            <div id="maintenance-section">
                <h3>Raise A Maintenance Ticket</h3>
                <p>Need help with a maintenance issue? Fill out the form below, and we'll handle it as soon as possible.</p>
                <ul>
                    <li>Provide accurate details to ensure faster resolution.</li>
                    <li>Upload an image for better understanding (optional).</li>
                </ul>
            
                <form method="POST" action="resident_homepage.php" enctype="multipart/form-data" class="maintenance-form">
            
                    <label for="requestDescription">Issue Description:</label>
                    <textarea id="requestDescription" name="requestDescription" placeholder="Briefly describe the issue" required></textarea>
            
                    <label for="requestPriority">Priority Level:</label>
                    <select id="requestPriority" name="requestPriority" required>
                        <option value="" disabled selected>Select Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
            
                    <label for="problemImage">Attach Image:</label>
                    <input type="file" id="problemImage" name="problemImage" accept="image/*">
            
                    <button type="submit" name="submit_maintenance">Submit Request</button>
                </form>
            </div>

            <div id="notification-settings">
                <h3>Notification Settings</h3>
                <p>Customize how you receive notifications from us. You can turn on or off different types of alerts below.</p>
                
                
                    <!-- Email Notifications -->
                    
                        <label for="emailNotifications">Email Notifications:</label>
                        <label class="switch">
                            <input type="checkbox" id="emailNotifications" checked>
                            <span class="slider round"></span>
                        </label>
                        <span class="description">Receive updates and alerts to your email.</span>
                    
                    
                    <!-- SMS Notifications -->
                    
                        <label for="smsNotifications">SMS Notifications:</label>
                        <label class="switch">
                            <input type="checkbox" id="smsNotifications">
                            <span class="slider round"></span>
                        </label>
                        <span class="description">Get SMS alerts for important updates.</span>
                    
            
                    <!-- App Notifications -->
                    
                        <label for="appNotifications">In-App Notifications:</label>
                        <label class="switch">
                            <input type="checkbox" id="appNotifications" checked>
                            <span class="slider round"></span>
                        </label>
                        <span class="description">Enable pop-up notifications within the app.</span>
                    
            
                    <!-- Notification Frequency -->
                    
                        <label for="notificationFrequency">Notification Frequency:</label>
                        <select id="notificationFrequency">
                            <option value="instant">Instant</option>
                            <option value="daily">Daily Digest</option>
                            <option value="weekly">Weekly Summary</option>
                            <option value="none">None</option>
                        </select>
                        <span class="description">Choose how frequently you'd like to receive notifications.</span>
                    
                    
                    <!-- Push Notifications -->
                    
                        <label for="pushNotifications">Push Notifications:</label>
                        <label class="switch">
                            <input type="checkbox" id="pushNotifications">
                            <span class="slider round"></span>
                        </label>
                        <span class="description">Enable instant push notifications on your device.</span>
                    
            
                    <!-- Custom Notification Preferences -->
                    
                        <label for="customPreferences">Custom Preferences:</label>
                        <textarea id="customPreferences" placeholder="Specify your custom notification preferences..."></textarea>
                        <span class="description">Set up specific alerts and preferences according to your needs.</span>
                    
            
                    <!-- Save Settings -->
                    
                        <button id="saveSettings" class="btn-save">Save Settings</button>
                    
                </div>
            </div>
            
        </section>
    </main>

    <footer>
        <p>Contact Information: +254 712-345-678 | rosewoodpark@gmail.com | Kilimani, Nairobi</p>
        <div class="social-media">
            <a href="#"><img src="landingpageimages/instagrampic.png" alt="instagram" height="20px" width="20px"></a> |
            <a href="#"><img src="landingpageimages/social-media.png" alt="tiktok" height="20px" width="20px"></a> |
            <a href="#"><img src="landingpageimages/logo.png" alt="whatsapp" height="20px" width="20px"></a>
        </div>
    </footer>

    <div id="bar-chart-container" style="display:none; padding: 20px;">
        <canvas id="bar-chart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function showBarChart() {
    // Hide all other sections in the content area
    const sections = document.querySelectorAll('.content > div');
    sections.forEach(section => section.style.display = 'none');

    // Display the bar chart section
    const chartSection = document.getElementById('bar-chart-section');
    chartSection.style.display = 'block';

    // Generate the bar chart if not already created
    const ctx = document.getElementById('bar-chart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'], // Replace with actual data
            datasets: [{
                label: 'Rental Payments',
                data: [500, 700, 800, 600, 900], // Replace with actual data
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Previous Rental Payments'
                }
            }
        }
    });
}

// Ensure initial state shows profile-section on page load
document.addEventListener('DOMContentLoaded', () => {
    showContent('profile-section');
});

        </script>

    <script>
        function showContent(sectionId) {
    const sections = document.querySelectorAll('.content > div'); // Select direct child divs of .content
    sections.forEach(section => {
        if (section.id === sectionId) {
            section.style.display = 'block'; // Show the selected section
        } else {
            section.style.display = 'none'; // Hide others
        }
    });
}

// Set initial state: show the profile section
document.addEventListener('DOMContentLoaded', () => {
    showContent('profile-section'); // Ensure this runs only after the DOM is fully loaded
});



        function logout() {
            alert("You have successfully logged out.");
            // Implement your logout functionality here
        }

        // Initial state: show the profile section
        showContent('profile-section');
    </script>
</body>
</html>

