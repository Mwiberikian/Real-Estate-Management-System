<?php
session_start(); // Start the session
include 'db_connect.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve user inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // SQL query to fetch user details and role
    $sql = "SELECT 'PropertyOwner' AS role, owner_id AS id, firstname, lastname, email, password FROM PropertyOwners WHERE email = ?
            UNION ALL
            SELECT 'Tenant' AS role, tenant_id AS id, firstname, lastname, email, password FROM Tenants WHERE email = ?
            UNION ALL
            SELECT 'Helpline' AS role, id AS id, firstname, lastname, email, password FROM helpline WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind the email parameter for all three queries
        $stmt->bind_param('sss', $email, $email, $email);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if a user was found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['role'] = $user['role'];

                // Set tenant_id specifically if the user is a Tenant
                if ($user['role'] === 'Tenant') {
                    $_SESSION['tenant_id'] = $user['id']; // tenant_id is stored in the 'id' field
                    header('Location: resident_homepage.php'); // Redirect to the resident homepage
                } elseif ($user['role'] === 'PropertyOwner') {
                    // Set session variable for property owner
                    $_SESSION['owner_id'] = $user['id'];
                    // Redirect to the owner's dashboard
                    header('Location: ownerdashboard.php?owner_id=' . $_SESSION['owner_id']);
                    exit;
                } elseif ($user['role'] === 'Helpline') {
                    header('Location: support.php'); // Redirect to the helpline page
                }
                exit;
            } else {
                // Password does not match
                echo "<script>alert('Invalid password!');</script>";
            }
        } else {
            // No account found with this email
            echo "<script>alert('No account found with this email!');</script>";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing the query!');</script>";
    }

    // Close the database connection
    $conn->close();
}
?>
