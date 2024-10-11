<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // Check if form data is being collected properly
    echo "Received: First Name - $firstName, Last Name - $lastName, Email - $email, Phone - $phone, Role - $role<br>";

    // SQL query to insert data into the 'users' table
    $sql = "INSERT INTO registerresidents (firstname, lastname, email, password, phonenumber, role) 
            VALUES (:firstname, :lastname, :email, :password, :phonenumber, :role)";

    try {
        // Prepare the statement
        $stmt = $pdo->prepare($sql);

        // Bind the form data to the SQL query
        $stmt->bindParam(':firstname', $firstName);
        $stmt->bindParam(':lastname', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':phonenumber', $phone);
        $stmt->bindParam(':role', $role);

        // Execute the query
        if ($stmt->execute()) {
            echo "Registration successful!";
            header("Location: index.html");
            exit();
        } else {
            echo "Failed to register!";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Form is not being submitted properly.";
}
?>
