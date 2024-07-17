<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$database = "bankdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['senderAccount']) && isset($data['recipientAccount']) && isset($data['amount']) && isset($data['pin'])) {
        $senderAccount = $data['senderAccount'];
        $recipientAccount = $data['recipientAccount'];
        $amount = $data['amount'];
        $pin = $data['pin'];

        // Verify PIN before processing the transaction in the first table
        $stmt = $conn->prepare("SELECT balance FROM bank WHERE Account_no = ? AND pin = ?");
        $stmt->bind_param("ss", $senderAccount, $pin);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // PIN is correct, proceed with the transaction
            $stmt->bind_result($senderBalance);
            $stmt->fetch();

            if ($senderBalance >= $amount) {
                // Begin a transaction to ensure atomicity
                $conn->begin_transaction();

                // Deduct from sender in the first table
                $stmt = $conn->prepare("UPDATE bank SET balance = balance - ? WHERE account_no = ?");
                $stmt->bind_param("ds", $amount, $senderAccount);
                $stmt->execute();

                // Add to recipient in the first table
                $stmt = $conn->prepare("UPDATE bank SET balance = balance + ? WHERE account_no = ?");
                $stmt->bind_param("ds", $amount, $recipientAccount);
                $stmt->execute();

                // Deduct from sender in the second table
                $stmt = $conn->prepare("UPDATE ourbank SET balance = balance - ? WHERE account_no= ?");
                $stmt->bind_param("ds", $amount, $senderAccount);
                $stmt->execute();

                // Add to recipient in the second table
                $stmt = $conn->prepare("UPDATE ourbank SET balance = balance + ? WHERE account_no = ?");
                $stmt->bind_param("ds", $amount, $recipientAccount);
                $stmt->execute();

                // Commit the transaction
                $conn->commit();

                echo "Transaction successful";
            } else {
                echo "Insufficient funds";
            }
        } else {
            echo "Incorrect PIN or account not found";
        }

        $stmt->close();
    } else {
        echo "Invalid request data";
    }
}

$conn->close();
?>
