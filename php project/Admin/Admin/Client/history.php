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

    if (isset($data['accountNumber']) && isset($data['date'])) {
        $accountNumber = $data['accountNumber'];
        $date = $data['date'];

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM transact WHERE account_no = ? AND DATE(date) = ?");
        $stmt->bind_param("ss", $accountNumber, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $transactions = [];
            while ($row = $result->fetch_assoc()) {
                $transactions[] = $row;
            }
            echo json_encode(["transactions" => $transactions]);
        } else {
            echo json_encode(["error" => "No transactions found for the given date"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Invalid request data"]);
    }
}

$conn->close();
?>
