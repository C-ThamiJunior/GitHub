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

    if (isset($data['accountNumber']) && isset($data['pin'])) {
        $accountNumber = $data['accountNumber'];
        $pin = $data['pin'];

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT balance FROM bank WHERE account_number = ? AND pin = ?");
        $stmt->bind_param("ss", $accountNumber, $pin);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($balance);
            $stmt->fetch();
            echo json_encode(["balance" => $balance]);
        } else {
            echo json_encode(["error" => "Account not found or incorrect PIN"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
