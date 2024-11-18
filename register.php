<?php
include 'db_connect.php'; // Include your MySQLi connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $resetCode = $_POST['resetCode'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
    } else {
        // Check if the reset code and email are valid
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND expires >= ?");
        if (!$stmt) {
            die("SQL error: " . $conn->error); // Debugging statement
        }

        $current_time = date("U");
        $stmt->bind_param('si', $email, $current_time); // Bind parameters
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedCode = $row['token'];

            if (password_verify($resetCode, $hashedCode)) {
                // Hash the new password and update it in the users table
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $updateStmt = $conn->prepare("
                    UPDATE users 
                    SET password = ? 
                    WHERE email = ?
                ");
                if (!$updateStmt) {
                    die("SQL error: " . $conn->error); // Debugging statement
                }

                $updateStmt->bind_param('ss', $hashedPassword, $email);
                $updateStmt->execute();

                // Delete the reset record
                $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                if (!$deleteStmt) {
                    die("SQL error: " . $conn->error); // Debugging statement
                }

                $deleteStmt->bind_param('s', $email);
                $deleteStmt->execute();

                echo "Password has been reset successfully!";
            } else {
                echo "Invalid reset code.";
            }
        } else {
            echo "Invalid or expired reset code.";
        }
    }
}
?>
