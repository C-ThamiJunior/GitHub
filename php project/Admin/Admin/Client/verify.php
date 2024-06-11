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
    if (isset($data['accountNumber']) && isset($data['pin'])) {
        $accountNumber = $data['accountNumber'];
        $pin = $data['pin'];

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM account WHERE account_no = ? AND pin = ?");
        $stmt->bind_param("ss", $accountNumber, $pin);
        $stmt->execute();

        // Check for errors in query execution
        if ($stmt->error) {
            echo json_encode(["error" => "Database error: " . $stmt->error]);
            exit();
        }

        // Retrieve result
        $result = $stmt->get_result();
        
        // Check if there is a matching account
        if ($result->num_rows > 0) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
