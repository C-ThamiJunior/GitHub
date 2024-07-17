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

    // Assuming you're sending data via POST
    if (isset($data['accountNumber'])) {
        $accountNumber = $data['accountNumber'];

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT balance FROM account WHERE Account_No = ?");
        $stmt->bind_param("s", $accountNumber);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($balance);
            $stmt->fetch();
            echo json_encode(["balance" => $balance]);
        } else {
            echo json_encode(["error" => "Account not found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
