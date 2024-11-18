<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Rosewood Parks</title>
    <link rel="stylesheet" href="support-style.css">
    <script src="support.js" defer></script>
</head>
<body>

<?php
// Include the database connection
include 'db_connect.php';

// Fetch maintenance requests from the database
$sql = "SELECT id, description, status, property_id FROM maintenance_requests";
$result = $conn->query($sql); // Execute the query
?>

<div class="container">
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="landingpageimages/image-removebg-preview.png" alt="Rosewood Parks Logo">
            <h1>Rosewood Parks</h1>
        </div>
        <nav>
            <ul>
                <li><a href="register.html">Register</a></li>
                <li><a href="Helpline_tickets.php">Tickets</a></li>
                <li><a href="announcements.php">Announcements</a></li>
                <li><a href="#communication">Communication</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section id="maintenance-request" class="card" style="background-image: url('SupportImages/repair-requests.jpeg');">
            <h2>View Tickets</h2>
            <p>View and manage all maintenance requests submitted by residents to ensure quick and effective service.</p>
            <button onclick="openModal('viewRequestsModal')">View Requests</button>
        </section>

        <section id="property-maintenance" class="card" style="background-image: url('SupportImages/maintenance.jpeg');">
            <h2>Property Maintenance</h2>
            <p>Review and track property maintenance tasks, ensuring timely and efficient upkeep of the properties.</p>
            <button onclick="openModal('viewTasksModal')">View Tasks</button>
        </section>

        <section id="owner-communication" class="card" style="background-image: url('SupportImages/communication.jpeg');">
            <h2>Communication with Property Owners</h2>
            <p>Maintain clear and direct communication with property owners to facilitate smooth management processes.</p>
            <button onclick="openModal('communicateModal')">Communicate</button>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>Address: 123 Main St, City</p>
        <p>Phone: 123-456-7890</p>
        <p>Email: info@example.com</p>
        <p>&copy; 2022 Real Estate Management. All rights reserved.</p>
    </footer>
</div>

<!-- Modals -->
<div id="viewRequestsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('viewRequestsModal')">&times;</span>
        <h2>View Maintenance Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                    <th>House Number</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    // Display data from the database
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['property_number']}</td>
                                <td>{$row['status']}</td>
                              </tr>";
                    }
                } else {
                    // No data found
                    echo "<tr><td colspan='4'>No requests found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="communicateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('communicateModal')">&times;</span>
        <h2>Communicate</h2>
        <p>Messages from property owners will appear here.</p>
    </div>
</div>

<?php
// Close the database connection
$conn->close();
?>

</body>
</html>
