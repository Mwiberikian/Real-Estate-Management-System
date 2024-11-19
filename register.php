<?php
include 'db_connect.php'; 

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $firstname = htmlspecialchars(trim($_POST['firstName']));
    $lastname = htmlspecialchars(trim($_POST['lastName']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $phonenumber = htmlspecialchars(trim($_POST['phone']));
    $role = htmlspecialchars(trim($_POST['role']));

    // Check if passwords match
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // SQL query based on role
    if ($role === 'PropertyOwner') {
        $sql = "INSERT INTO PropertyOwners (firstname, lastname, email, password, phonenumber) 
                VALUES (?, ?, ?, ?, ?)";
    } elseif ($role === 'Resident') {
        $sql = "INSERT INTO Tenants (firstname, lastname, email, password, phonenumber,property_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
    } elseif ($role === 'Helpline') {
        $sql = "INSERT INTO helpline (firstname, lastname, email, password, phonenumber) 
                VALUES (?, ?, ?, ?, ?)";
    } else {
        echo "Invalid role selected.";
        exit();
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    if ($role === 'PropertyOwner') {
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashedPassword, $phonenumber);
    }
    elseif ($role === 'Helpline') {
        $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashedPassword, $phonenumber);
    }
    } elseif($role === 'Resident') {
        $stmt->bind_param("ssssss", $firstname, $lastname, $email, $hashedPassword, $phonenumber, $property_id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect on success
        header("Location: login.html");
        exit();
    } else {
        // Handle execution errors
        echo "Registration failed: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
?>