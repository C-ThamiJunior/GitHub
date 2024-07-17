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
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Handle incoming request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['accountNumber']) && isset($data['accountPin'])) {
        $accountNumber = $data['accountNumber'];
        $accountPin = $data['accountPin'];

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT Account_No FROM account WHERE Account_No = ? AND PIN = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $accountNumber, $accountPin);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(["verified" => true]);
            } else {
                echo json_encode(["verified" => false, "error" => "Invalid account number or PIN"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
