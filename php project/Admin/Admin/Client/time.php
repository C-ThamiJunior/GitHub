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

    if (isset($data['senderAccount']) && isset($data['contactNumber']) && isset($data['amount']) && isset($data['pin'])) {
        $senderAccount = $data['senderAccount'];
        $contactNumber = $data['contactNumber'];
        $amount = $data['amount'];
        $pin = $data['pin'];

        // Verify PIN before processing the airtime purchase
        $stmt = $conn->prepare("SELECT balance FROM Account WHERE account_no = ? AND pin = ?");
        $stmt->bind_param("ss", $senderAccount, $pin);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // PIN is correct, proceed with the airtime purchase
            $stmt->bind_result($senderBalance);
            $stmt->fetch();

            if ($senderBalance >= $amount) {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Deduct from sender
                    $stmt = $conn->prepare("UPDATE Account SET balance = balance - ? WHERE account_no = ?");
                    $stmt->bind_param("ds", $amount, $senderAccount);
                    $stmt->execute();

                    // Deduct from sender in the second table
                    $stmt = $conn->prepare("UPDATE ourbank SET balance = balance - ? WHERE account_no= ?");
                    $stmt->bind_param("ds", $amount, $senderAccount);
                    $stmt->execute();

                    // Add airtime to recipient
                    $stmt = $conn->prepare("SELECT airtime FROM Account WHERE contact = ?");
                    $stmt->bind_param("s", $contactNumber);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($receiverAirtime);
                        $stmt->fetch();

                        $stmt = $conn->prepare("UPDATE Account SET airtime = airtime + ? WHERE contact = ?");
                        $stmt->bind_param("ds", $amount, $contactNumber);
                        $stmt->execute();

                        // Add airtime to recipient in the second table
                       
                        if ($stmt->num_rows > 0) {
                            $stmt->bind_result($receiverAirtime);
                            $stmt->fetch();

                           

                            // Commit transaction
                            $conn->commit();
                            echo json_encode(["message" => "Airtime purchase successful"]);
                        } else {
                            throw new Exception('Receiver account not found in the second table');
                        }
                    } else {
                        throw new Exception('Receiver account not found in the first table');
                    }
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    echo json_encode(["error" => $e->getMessage()]);
                }
            } else {
                echo json_encode(["error" => "Insufficient funds"]);
            }
        } else {
            echo json_encode(["error" => "Incorrect PIN or account not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
