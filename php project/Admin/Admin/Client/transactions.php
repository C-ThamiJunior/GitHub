<?php
// Verify PIN for transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract sender account number, PIN, recipient account number, amount, and contact number from POST request
    $senderAccount = $_POST['senderAccount'];
    $pin = $_POST['pin'];
    $recipientAccount = $_POST['recipientAccount'];
    $amount = $_POST['amount'];
    $contactNumber = isset($_POST['contactNumber']) ? $_POST['contactNumber'] : null;

    // Verify PIN
    $validPin = verifyPin($senderAccount, $pin);

    if ($validPin) {
        // Connect to the database (replace 'localhost', 'username', 'password', 'dbname' with your actual database credentials)
        $conn = new mysqli('localhost', 'root', '', 'bankdb');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Store transaction details in database
        $transactionId = storeTransaction($conn, $senderAccount, $recipientAccount, $amount, $contactNumber);

        if ($transactionId) {
            // Transaction successful, return success message with transaction ID
            echo "Transaction successful. Transaction ID: $transactionId";
        } else {
            // Transaction failed, return error message
            echo "Transaction failed. Please try again later.";
        }

        // Close database connection
        $conn->close();
    } else {
        // Invalid PIN, return error message
        echo "Invalid PIN. Please try again.";
    }
}

// Function to verify PIN
function verifyPin($accountNumber, $pin) {
    // Your PIN verification logic goes here
    // For example, if you have a database table 'accounts' with columns 'account_number' and 'pin':
    $conn = new mysqli('localhost', 'username', 'password', 'dbname');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE account_number = ? AND pin = ?");
    $stmt->bind_param("ss", $accountNumber, $pin);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    return $count > 0;
}

// Function to store transaction details in database
function storeTransaction($conn, $senderAccount, $recipientAccount, $amount, $contactNumber) {
    // Prepare SQL statement to insert transaction data into 'transactions' table
    $stmt = $conn->prepare("INSERT INTO transactions (sender_account, recipient_account, amount, recipient_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $senderAccount, $recipientAccount, $amount, $contactNumber);
    $stmt->execute();

    // Check if transaction was successfully inserted
    if ($stmt->affected_rows > 0) {
        // Get the ID of the last inserted row (i.e., the transaction ID)
        $transactionId = $conn->insert_id;
    } else {
        // If transaction insertion failed, set transaction ID to false
        $transactionId = false;
    }

    // Close statement
    $stmt->close();

    // Return generated transaction ID or false if insertion failed
    return $transactionId;
}
?>
