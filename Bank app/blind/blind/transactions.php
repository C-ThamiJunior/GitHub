<?php
header('Content-Type: application/json');

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$database = "bankdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate incoming data
if (!isset($data['transactionDate']) || !isset($data['accountNumber'])) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$transactionDate = $data['transactionDate'];
$accountNumber = $data['accountNumber'];

// Prepare query to fetch transactions for the specified account number and date
$stmt = $conn->prepare("SELECT transaction_id, first_name, last_name, recipient, amount, date, account_no FROM transaction WHERE DATE(date) = ? AND account_no = ?");
$stmt->bind_param("ss", $transactionDate, $accountNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $transactions = array();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = array(
            'transaction_id' => $row['transaction_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'recipient' => $row['recipient'],
            'amount' => $row['amount'],
            'date' => $row['date'],
            'account_no' => $row['account_no']
        );
    }
    echo json_encode(['transactions' => $transactions]);
} else {
    echo json_encode(['message' => 'No transactions found on the specified date for the account number provided.']);
}

$stmt->close();
$conn->close();
?>
