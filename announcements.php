<?php
// Database connection
$host = 'localhost';
$db = 'software';
$user = 'root';
$pass = 'oliviamumbi2010'; // Update with your database password
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_announcement'])) {
    $title = $conn->real_escape_string($_POST['announce_title']);
    $content = $conn->real_escape_string($_POST['announce_content']);
    $author_id = intval($_POST['announce_author_id']);

    $stmt = $conn->prepare("INSERT INTO helpline_announce (announce_title, announce_content, announce_date_created, announce_author_id) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("ssi", $title, $content, $author_id);

    if ($stmt->execute()) {
        echo "<script>alert('Announcement submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error submitting announcement: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Announcement</title>
    <link rel="stylesheet" href="announcements.css"> <!-- Optional: Add CSS for styling -->
</head>
<body>
    <header>
        <h1>Submit a New Announcement</h1>
    </header>

    <main>
        <form method="POST" action="announcements.php">
            <label for="announce_title">Announcement Title:</label>
            <input type="text" id="announce_title" name="announce_title" required>

            <label for="announce_content">Announcement Content:</label>
            <textarea id="announce_content" name="announce_content" required></textarea>

            <label for="announce_author_id">Author ID:</label>
            <input type="number" id="announce_author_id" name="announce_author_id" required>

            <button type="submit" name="submit_announcement">Submit Announcement</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Resident Portal. All rights reserved.</p>
    </footer>
</body>
</html>
