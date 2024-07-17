<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "bankdb";
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Function to retrieve balance for a given account number
function getBalance($conn, $accountNumber) {
    $stmt = $conn->prepare("SELECT balance FROM account WHERE account_no = ?");
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

// Function to retrieve sender's first name and last name
function getSenderName($conn, $senderAccount) {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM bank WHERE account_no = ?");
    $stmt->bind_param("s", $senderAccount);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstName = $row['first_name'];
        $lastName = $row['last_name'];
        $stmt->close();
        return [$firstName, $lastName];
    } else {
        $stmt->close();
        return [null, null]; // Handle if sender account is not found
    }
}

// Function to generate a unique transaction ID (assuming it's an INT)
function generateTransactionID() {
    return rand(100000, 999999); // Generates a random transaction ID within a range
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate incoming data
if (!isset($data['senderAccount']) || !isset($data['recipientAccount']) || !isset($data['amount'])) {
    error_log("Missing required parameters.");
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

$senderAccount = $data['senderAccount'];
$recipientAccount = $data['recipientAccount'];
$amount = $data['amount'];

// Validate the amount
if (!is_numeric($amount) || $amount <= 0) {
    error_log("Invalid amount specified: $amount");
    echo json_encode(['error' => 'Invalid amount specified.']);
    exit;
}

// Get sender's balance
$senderBalance = getBalance($conn, $senderAccount);

// Check if sender has sufficient balance
if ($senderBalance === null) {
    error_log("Sender account not found: $senderAccount");
    echo json_encode(['error' => 'Sender account not found.']);
    exit;
} else if ($senderBalance < $amount) {
    error_log("Insufficient balance: Sender balance is $senderBalance, attempted transfer amount is $amount");
    echo json_encode(['error' => 'Insufficient balance.']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Update sender's balance in account table
    $newSenderBalance = $senderBalance - $amount;
    $stmt = $conn->prepare("UPDATE account SET balance = ? WHERE account_no = ?");
    $stmt->bind_param("ds", $newSenderBalance, $senderAccount);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update sender balance in account table: ' . $stmt->error);
    }
    $stmt->close();

    // Update sender's balance in bank table (assuming it's the same as account table)
    $stmt = $conn->prepare("UPDATE bank SET balance = ? WHERE account_no = ?");
    $stmt->bind_param("ds", $newSenderBalance, $senderAccount);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update sender balance in bank table: ' . $stmt->error);
    }
    $stmt->close();

    // Get sender's first name and last name
    list($senderFirstName, $senderLastName) = getSenderName($conn, $senderAccount);
    if ($senderFirstName === null || $senderLastName === null) {
        throw new Exception("Sender name not found for account: $senderAccount");
    }

    // Generate transaction ID
    $transactionID = generateTransactionID();

    // Insert into transaction table
    $currentDate = date('Y-m-d H:i:s'); // Current datetime
    $stmt = $conn->prepare("INSERT INTO transaction (transaction_id, first_name, last_name, recipient, amount, date, account_no) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdss", $transactionID, $senderFirstName, $senderLastName, $recipientAccount, $amount, $currentDate, $senderAccount);
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert into transaction table: ' . $stmt->error);
    }
    $stmt->close();

    // Get recipient's balance and update it
    $recipientBalance = getBalance($conn, $recipientAccount);
    if ($recipientBalance === null) {
        throw new Exception('Recipient account not found: ' . $recipientAccount);
    }

    $newRecipientBalance = $recipientBalance + $amount;
    $stmt = $conn->prepare("UPDATE account SET balance = ? WHERE account_no = ?");
    $stmt->bind_param("ds", $newRecipientBalance, $recipientAccount);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update recipient balance in account table: ' . $stmt->error);
    }
    $stmt->close();

    // Update recipient's balance in bank table (assuming it's the same as account table)
    $stmt = $conn->prepare("UPDATE bank SET balance = ? WHERE account_no = ?");
    $stmt->bind_param("ds", $newRecipientBalance, $recipientAccount);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update recipient balance in bank table: ' . $stmt->error);
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(['message' => "Transaction successful: Transfer $amount rands from account $senderAccount to account $recipientAccount.", 'transaction_id' => $transactionID]);
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();

    // Log error
    error_log($e->getMessage());

    // Return error response
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
