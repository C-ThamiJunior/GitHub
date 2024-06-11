<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "voicedb";
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Function to retrieve balance for a given account number
function getBalance($conn, $accountNumber) {
    $stmt = $conn->prepare("SELECT balance FROM bank WHERE account_number = ?");
    $stmt->bind_param("s", $accountNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $balance = $row['balance'];
    } else {
        $balance = null; // Handle if account number is not found
    }
    
    $stmt->close();
    return $balance;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate incoming data
if (!isset($data['senderAccount']) || !isset($data['recipientAccount']) || !isset($data['amount'])) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$senderAccount = $data['senderAccount'];
$recipientAccount = $data['recipientAccount'];
$amount = $data['amount'];

// Validate the amount
if (!is_numeric($amount) || $amount <= 0) {
    echo json_encode(['error' => 'Invalid amount specified.']);
    exit;
}

// Get sender's balance
$senderBalance = getBalance($conn, $senderAccount);

// Check if sender has sufficient balance
if ($senderBalance === null) {
    echo json_encode(['error' => 'Sender account not found.']);
} else if ($senderBalance < $amount) {
    echo json_encode(['error' => 'Insufficient balance.']);
} else {
    // Proceed with the transaction
    // Update sender's balance
    $newSenderBalance = $senderBalance - $amount;
    $stmt = $conn->prepare("UPDATE bank SET balance = ? WHERE account_number = ?");
    $stmt->bind_param("ds", $newSenderBalance, $senderAccount);
    $stmt->execute();
    $stmt->close();

    // Get recipient's balance
    $recipientBalance = getBalance($conn, $recipientAccount);
    if ($recipientBalance === null) {
        echo json_encode(['error' => 'Recipient account not found.']);
    } else {
        // Update recipient's balance
        $newRecipientBalance = $recipientBalance + $amount;
        $stmt = $conn->prepare("UPDATE bank SET balance = ? WHERE account_number = ?");
        $stmt->bind_param("ds", $newRecipientBalance, $recipientAccount);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['message' => "Transaction successful: Transfer $amount rands from account $senderAccount to account $recipientAccount."]);
    }
}

$conn->close();
?>
