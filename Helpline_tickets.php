<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpline Tickets - Rosewood Parks</title>
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
            padding: 20px;
        }

        /* Header Styling */
        header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background-color: #A58754;
            color: beige;
        }

        header h1 {
            font-size: 2em;
            margin: 0;
        }

        /* Main Content Styling */
        main {
            margin-top: 20px;
        }

        main h2 {
            text-align: center;
            margin-bottom: 20px;
        }

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

        /* Button Styling */
        button {
            background-color: white;
            color: #000;
            padding: 10px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #A58754;
            color: white;
        }

        /* Footer Styling */
        footer {
            background-color: blanchedalmond;
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            color: #555;
            border-top: 1px solid #cfd8dc;
        }

        footer p {
            margin: 5px 0;
            color: #004d40;
        }
    </style>
    <script>
        function toggleCheckboxes(rowId, selectedId) {
            const checkboxes = document.querySelectorAll(.status-checkbox-${rowId});
            checkboxes.forEach(checkbox => {
                if (checkbox.id !== selectedId) {
                    checkbox.checked = false;
                }
            });
        }
    </script>
</head>
<body>

<div class="container">
    <header>
        <h1>Rosewood Parks - Helpline Tickets</h1>
    </header>

    <main>
        <h2>Maintenance Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Property ID</th>
                    <th>Details</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Database connection
                $host = 'localhost:3307';
                $db = 'software';
                $user = 'root';
                $pass = 'oliviamumbi2010'; // Update password
                $conn = new mysqli($host, $user, $pass, $db);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Handle status update
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_id']) && isset($_POST['status'])) {
                    $ticketId = intval($_POST['ticket_id']);
                    $status = $conn->real_escape_string($_POST['status']);
                    $conn->query("UPDATE maintenance_requests SET status = '$status' WHERE id = $ticketId");
                }

                // Fetch maintenance requests
                $result = $conn->query("SELECT * FROM maintenance_requests");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rowId = $row['id'];
                        $imageTag = $row['image_path'] ? "<img src='{$row['image_path']}' alt='Problem Image' width='50' height='50'>" : 'No Image';
                        echo "<tr>
                                <td>{$row['property_id']}</td>
                                <td>{$row['description']}</td>
                                <td>{$imageTag}</td>
                                <td>" . htmlspecialchars($row['status']) . "</td>
                                <td>
                                    <form method='POST' action=''>
                                        <input type='hidden' name='ticket_id' value='{$rowId}'>
                                        <label>
                                            <input type='checkbox' class='status-checkbox-{$rowId}' id='waiting-{$rowId}' name='status' value='waiting' onchange=\"toggleCheckboxes({$rowId}, 'waiting-{$rowId}')\" " . ($row['status'] === 'waiting' ? 'checked' : '') . "> Waiting
                                        </label>
                                        <label>
                                            <input type='checkbox' class='status-checkbox-{$rowId}' id='in_progress-{$rowId}' name='status' value='in_progress' onchange=\"toggleCheckboxes({$rowId}, 'in_progress-{$rowId}')\" " . ($row['status'] === 'in_progress' ? 'checked' : '') . "> In Progress
                                        </label>
                                        <label>
                                            <input type='checkbox' class='status-checkbox-{$rowId}' id='done-{$rowId}' name='status' value='done' onchange=\"toggleCheckboxes({$rowId}, 'done-{$rowId}')\" " . ($row['status'] === 'done' ? 'checked' : '') . "> Done
                                        </label>
                                        <button type='submit'>Update</button>
                                    </form>
                                </td>
                                <td>{$row['created_at']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No tickets found.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>&copy; 2024 Rosewood Parks. All Rights Reserved.</p>
    </footer>
</div>

</body>
</html>