<?php
// Include database connection
$servername = "localhost"; // Update with your database server
$username = "root";        // Update with your database username
$password = "oliviamumbi2010";            // Update with your database password
$dbname = "software";      // Update with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read callback data
$callbackData = file_get_contents('php://input');
$data = json_decode($callbackData, true);

// Log callback data for debugging (optional)
file_put_contents('callback_log.txt', $callbackData, FILE_APPEND);

if (isset($data['Body']['stkCallback'])) {
    $stkCallback = $data['Body']['stkCallback'];
    $merchantRequestID = $stkCallback['MerchantRequestID'];
    $checkoutRequestID = $stkCallback['CheckoutRequestID'];
    $resultCode = $stkCallback['ResultCode'];
    $resultDesc = $stkCallback['ResultDesc'];

    // Check if the transaction was successful
    if ($resultCode == 0) {
        $amount = $stkCallback['CallbackMetadata']['Item'][0]['Value'];
        $phone = $stkCallback['CallbackMetadata']['Item'][1]['Value'];

        // Query to update the Payments table
        $stmt = $conn->prepare("INSERT INTO Payments (tenant_id, amount_paid, payment_status) VALUES (?, ?, 'Paid')");
        $tenant_id = 1; // Replace with logic to get the correct tenant_id (e.g., mapping phone to tenant)
        $stmt->bind_param("id", $tenant_id, $amount);

        if ($stmt->execute()) {
            echo "Payment recorded successfully.";
        } else {
            echo "Error recording payment: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Handle failed transaction
        echo "Transaction failed: $resultDesc";
    }
} else {
    echo "Invalid callback data received.";
}

$conn->close();
?>
