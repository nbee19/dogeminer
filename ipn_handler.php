<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if this is a valid CoinPayments IPN
if (!verifyIPN($_POST)) {
    http_response_code(400);
    exit("Invalid IPN signature");
}

// Process different types of transactions
switch ($_POST['txn_type']) {
    case 'deposit':
        processDeposit($_POST);
        break;
    case 'withdrawal':
        processWithdrawal($_POST);
        break;
    default:
        logIPN("Unhandled transaction type: " . $_POST['txn_type']);
        break;
}

http_response_code(200); // Always respond with 200 OK

/**
 * Process deposit transaction
 */
function processDeposit($postData) {
    $userId = intval($postData['custom']);
    $amount = floatval($postData['amount']);
    $txid = $postData['txn_id'];
    
    // Update user balance
    updateBalance($userId, $amount);
    
    // Log the transaction
    logTransaction($userId, 'deposit', $amount, $txid);
    
    logIPN("Successfully processed deposit: $txid for user $userId");
}

/**
 * Process withdrawal transaction
 */
function processWithdrawal($postData) {
    $userId = intval($postData['custom']);
    $amount = floatval($postData['amount']);
    $txid = $postData['txn_id'];
    
    // Mark withdrawal as completed
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE transactions SET status = 'completed', txid = ? WHERE user_id = ? AND type = 'withdrawal' AND status = 'pending'");
    $stmt->bind_param("si", $txid, $userId);
    $stmt->execute();
    
    logIPN("Successfully processed withdrawal: $txid for user $userId");
}

/**
 * Log IPN messages
 */
function logIPN($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents('ipn.log', $logEntry, FILE_APPEND);
}
