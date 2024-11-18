<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'db_connect.php'; // Include your MySQLi connection file
require 'vendor/autoload.php'; // Autoload PHPMailer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit();
    }

    try {
        // Check if the email exists in the user tables
        $user = null;

        $queries = [
            'tenants' => "SELECT email FROM tenants WHERE email = ?",
            'propertyowners' => "SELECT email FROM propertyowners WHERE email = ?",
            'helpline' => "SELECT email FROM helpline WHERE email = ?"
        ];

        foreach ($queries as $sql) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("SQL error: " . $conn->error);
            }
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                break;
            }
        }

        if ($user) {
            // Generate a random code
            $resetCode = rand(100000, 999999); // 6-digit numeric code
            $hashedCode = password_hash($resetCode, PASSWORD_DEFAULT);
            $expires = date("U") + 1800; // 30 minutes

            // Delete existing reset requests
            $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $deleteStmt->bind_param('s', $email);
            $deleteStmt->execute();

            // Insert the new reset code into the password_resets table
            $insertStmt = $conn->prepare("
                INSERT INTO password_resets (email, token, expires) 
                VALUES (?, ?, ?)
            ");
            $insertStmt->bind_param('ssi', $email, $hashedCode, $expires);
            $insertStmt->execute();

            // Send the reset code to the user's email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'benniahgrey@gmail.com';
                $mail->Password = 'jhsyrznbtiibtybm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('noreply@example.com', 'Rosewood Park Residencies');
                $mail->addAddress($email);
                $mail->Subject = 'Your Password Reset Code';
                $mail->isHTML(true);
                $mail->Body = "
                    <p>Hi,</p>
                    <p>Your password reset code is:</p>
                    <h2>$resetCode</h2>
                    <p>Please visit the link below to reset your password:</p>
                    <p><a href='http://localhost/Real-Estate-Management-System/reset_password.php'>Reset Password Page</a></p>
                    <p>This code will expire in 30 minutes.</p>
                    <p>Thanks,<br>Your Website Team</p>
                ";

                $mail->send();
                echo "If your email is registered, you will receive a reset code shortly.";
            } catch (Exception $e) {
                echo "Error: Unable to send email. Please try again later.";
            }
        } else {
            echo "If your email is registered, you will receive a reset code shortly.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
?>
