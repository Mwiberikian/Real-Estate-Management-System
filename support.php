<?php
// Include the database connection
include 'db_connect.php';

// Fetch maintenance requests from the database
$sql = "SELECT id, description, status, property_id FROM maintenance_requests";
$result = $conn->query($sql); // Execute the query
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Rosewood Parks</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: beige;
            color: black;
        }

        .container {
            height: 400px;
        }

        /* Header Styling */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 50px;
            background-color: #A58754;
            color: black;
        }

        header .logo {
            display: flex;
            align-items: center;
        }

        header .logo img {
            height: 50px;
            margin-right: 10px;
        }

        header h1 {
            font-size: 1.5em;
            margin: 0;
            color: beige;
            color: black;
        }

        /* Navigation Styling */
        nav ul {
            display: flex;
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li a {
            color: beige;
            font-family: 'Merriweather', Georgia, serif;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
            color: black;
        }

        nav ul li a:hover {
            color: #647e0c;
            text-decoration: underline;
        }

        /* Main Content Styling */
        main {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin: 30px 0;
            height: 30px;
            color: black;
        }

        /* Card Styling */
        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            position: relative;
            width: 240px;
            height: 300px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-size: cover;
            background-position: center;
            color: white;
            overflow: hidden;
            transition: transform 0.2s;
            color: black;
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            transition: background-color 0.3s;
            border-radius: 8px;
        }

        .card:hover::before {
            background-color: blanchedalmond;
        }

        .card h2,
        .card p {
            position: relative;
            z-index: 1;
            font-size: medium;
            margin: 10px 0;
            text-align: center;
        }

        .card button {
            align-self: stretch;
            text-align: center;
        }

        /* Button Styling */
        button {
            background-color: white;
            color: #ffffff;
            padding: 10px 10px;
            border: none;
            border-radius: 15px;
            margin: 40px;
            max-width: 130px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
            transition: background-color 0.3s;
            position: relative;
            z-index: 1;
            color: black;
        }

        button:hover {
            background-color:  #A58754;
        }

        /* Footer Styling */
        footer {
            background-color: blanchedalmond;
            text-align: center;
            padding: 20px;
            margin-top: 350px;
            font-size: 0.9em;
            color: #555;
            border-top: 1px solid #cfd8dc;
        }

        footer p {
            margin: 5px 0;
            color: #004d40;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Modal Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
    <script src="support.js" defer></script>
</head>
<body>
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
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section id="maintenance-request" class="card" style="background-image: url('SupportImages/repair-requests.jpeg');">
            <h2>View Tickets</h2>
            <p>View and manage all maintenance requests submitted by residents to ensure quick and effective service.</p>
            <button onclick="openModal('Helpline_tickets.php')">View Requests</button>
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
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['property_id']}</td>
                                <td>{$row['status']}</td>
                              </tr>";
                    }
                } else {
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
        <h2>Communication Modal</h2>
        <p>Placeholder for communication features</p>
    </div>
</div>
</body>
</html>