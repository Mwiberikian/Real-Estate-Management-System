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
        $current_time = date("U");
        $stmt->bind_param('si', $email, $current_time);
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
                $updateStmt->bind_param('ss', $hashedPassword, $email);
                $updateStmt->execute();

                // Delete the reset record
                $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <form action="" method="post">
        <h2>Reset Password</h2>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="resetCode">Reset Code:</label>
        <input type="text" id="resetCode" name="resetCode" required>

        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>

        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
