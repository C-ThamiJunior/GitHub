<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // Ensure the content type is JSON

$conn = new mysqli('localhost', 'ladzi', '123', 'bankdb');
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data['transferCode'] === 1) {
        $accountNumber = $data['accountNumber'];
        $accountReceiver = $data['accountReceiver'];
        $money = $data['money'];

        // Step 1: Check if client has enough money to perform the transaction
        $checkBalanceQuery = "SELECT balance FROM account WHERE account_no = ?";
        $stmt = $conn->prepare($checkBalanceQuery);
        $stmt->bind_param('i', $accountNumber);
        $stmt->execute();
        $checkBalanceResult = $stmt->get_result();

        if ($checkBalanceResult && $checkBalanceResult->num_rows > 0) {
            $senderBalance = $checkBalanceResult->fetch_assoc()['balance'];
            if ($senderBalance >= $money) {
                // Step 2: Proceed to change bank balance
                $sqlQuery = "SELECT first_name, last_name FROM ourbank WHERE account_no = ?";
                $stmt = $conn->prepare($sqlQuery);
                $stmt->bind_param('i', $accountNumber);
                $stmt->execute();
                $clientInfo = $stmt->get_result();
                $clientDetails = $clientInfo->fetch_assoc();
                $first_name = $clientDetails['first_name'];
                $last_name = $clientDetails['last_name'];

                $conn->begin_transaction();

                try {
                    // Deduct money from sender's account
                    $updateSenderQuery = "UPDATE account SET balance = balance - ? WHERE account_no = ?";
                    $stmt = $conn->prepare($updateSenderQuery);
                    $stmt->bind_param('di', $money, $accountNumber);
                    $stmt->execute();

                    $updateOurBank = "UPDATE ourbank SET balance = balance - ? WHERE account_no = ?";
                    $stmt = $conn->prepare($updateOurBank);
                    $stmt->bind_param('di', $money, $accountNumber);
                    $stmt->execute();

                    $updateBank = "UPDATE bank SET balance = balance - ? WHERE account_no = ?";
                    $stmt = $conn->prepare($updateBank);
                    $stmt->bind_param('di', $money, $accountNumber);
                    $stmt->execute();

                    // Check if receiver account is in the account table...
                    $checkReceiverQuery = "SELECT account_no FROM account WHERE account_no = ?";
                    $stmt = $conn->prepare($checkReceiverQuery);
                    $stmt->bind_param('i', $accountReceiver);
                    $stmt->execute();
                    $checkReceiverResult = $stmt->get_result();

                    if ($checkReceiverResult && $checkReceiverResult->num_rows > 0) {
                        $updateReceiverQuery = "UPDATE account SET balance = balance + ? WHERE account_no = ?";
                        $stmt = $conn->prepare($updateReceiverQuery);
                        $stmt->bind_param('di', $money, $accountReceiver);
                        $stmt->execute();

                        $updateOurBankForReceiver = "UPDATE ourbank SET balance = balance + ? WHERE account_no = ?";
                        $stmt = $conn->prepare($updateOurBankForReceiver);
                        $stmt->bind_param('di', $money, $accountReceiver);
                        $stmt->execute();

                        $updateBankForReceiver = "UPDATE bank SET balance = balance + ? WHERE account_no = ?";
                        $stmt = $conn->prepare($updateBankForReceiver);
                        $stmt->bind_param('di', $money, $accountReceiver);
                        $stmt->execute();
                    }

                    // Step 3: Write to transaction table
                    $transaction_id = mt_rand(1000, 9999);
                    $transactionQuery = "INSERT INTO transaction(transaction_id, first_name, last_name, recipient_acc, amount, date, account_no) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
                    $stmt = $conn->prepare($transactionQuery);
                    $stmt->bind_param('issidi', $transaction_id, $first_name, $last_name, $accountReceiver, $money, $accountNumber);
                    $stmt->execute();

                    $conn->commit();
                    echo json_encode(["status" => "success", "message" => "Transaction successful"]);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(["status" => "error", "message" => "Transaction failed: " . $conn->error]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Insufficient funds"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Sender account not found in process transaction"]);
        }
    } else if ($data['transferCode'] === 2) {
        // Step 4: Retrieve balance
        $accountNumber = $data['accountNumber'];

        $sql = "SELECT balance FROM account WHERE account_no = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $accountNumber);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $feedback = $result->fetch_assoc();
            $rand = $feedback['balance'];
            echo json_encode(["status" => "success", "message" => "you have $rand rands."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Couldn't find your account number"]);
        }
    }
    $conn->close();
}
