<?php
require_once 'config.php';

/**
 * Database Connection
 */
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

/**
 * Input Sanitization
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Authentication Functions
 */
function redirectToLoginIfNotLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function redirectToAdminLoginIfNotLoggedIn() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: admin/login.php");
        exit();
    }
}

function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

function adminLogout() {
    unset($_SESSION['admin_id']);
    session_destroy();
    header("Location: admin/login.php");
    exit();
}

/**
 * User Management Functions
 */
function banUser($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

function unbanUser($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

function updateUserBalance($userId, $newBalance) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $stmt->bind_param("di", $newBalance, $userId);
    $stmt->execute();
}

function getTotalUsers() {
    $conn = connectDB();
    $result = $conn->query("SELECT COUNT(*) FROM users");
    return $result->fetch_row()[0];
}

function getUsers($limit, $offset) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, dogecoin_address, balance, status FROM users ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Transaction Management Functions
 */
function getTotalTransactions($type = '', $status = '') {
    $conn = connectDB();
    $where = [];
    $params = [];
    
    if ($type) {
        $where[] = "t.type = ?";
        $params[] = $type;
    }
    
    if ($status) {
        $where[] = "t.status = ?";
        $params[] = $status;
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    $types = str_repeat("s", count($params));
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM transactions t $whereClause");
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTransactions($limit, $offset, $type = '', $status = '') {
    $conn = connectDB();
    $where = [];
    $params = [];
    
    if ($type) {
        $where[] = "t.type = ?";
        $params[] = $type;
    }
    
    if ($status) {
        $where[] = "t.status = ?";
        $params[] = $status;
    }
    
    $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
    $types = str_repeat("s", count($params));
    
    $stmt = $conn->prepare("SELECT t.*, u.dogecoin_address AS user_address FROM transactions t JOIN users u ON t.user_id = u.id $whereClause ORDER BY t.created_at DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPendingWithdrawals() {
    $conn = connectDB();
    $result = $conn->query("SELECT SUM(amount) FROM transactions WHERE type = 'withdrawal' AND status = 'pending'");
    return $result->fetch_row()[0] ?: 0;
}

/**
 * Mining Plan Management Functions
 */
function addMiningPlan($name, $priceDoge, $dailyReturn, $durationDays, $status) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO mining_plans (name, price_doge, daily_return, duration_days, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdids", $name, $priceDoge, $dailyReturn, $durationDays, $status);
    return $stmt->execute();
}

function getMiningPlans() {
    $conn = connectDB();
    $result = $conn->query("SELECT * FROM mining_plans ORDER BY id DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function togglePlanStatus($planId, $newStatus) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE mining_plans SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $planId);
    return $stmt->execute();
}

/**
 * Settings Management Functions
 */
function updateSettings($minWithdrawal, $referralCommission, $siteName, $siteDescription) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE settings SET min_withdrawal = ?, referral_commission = ?, site_name = ?, site_description = ?");
    $stmt->bind_param("ddss", $minWithdrawal, $referralCommission, $siteName, $siteDescription);
    return $stmt->execute();
}

function getCurrentSettings() {
    $conn = connectDB();
    $result = $conn->query("SELECT * FROM settings LIMIT 1");
    return $result->fetch_assoc();
}

/**
 * Referral System Functions
 */
function generateReferralLink($userId) {
    return "https://yourdomain.com/register?ref=" . base64_encode($userId);
}

function validateDogecoinAddress($address) {
    return preg_match('/^[D][a-km-zA-HJ-NP-Z1-9]{32,34}$/', $address);
}

function logReferral($referrerId, $referredId) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO referrals (referrer_id, referred_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $referrerId, $referredId);
    $stmt->execute();
}

function getReferralsCount($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}

function getTotalCommission($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT SUM(bonus_amount) FROM referrals WHERE referrer_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0] ?? 0;
}

/**
 * Payment Processing Functions
 */
function createPayment($userId, $amountUSD, $currency = 'USD') {
    $ch = curl_init();
    $postData = [
        'key' => COINPAYMENTS_API_KEY,
        'version' => 1,
        'cmd' => 'create_transaction',
        'currency1' => $currency,
        'currency2' => 'DOGE',
        'amount' => $amountUSD,
        'buyer_email' => 'user@example.com',
        'item_name' => 'Cloud Mining Deposit',
        'ipn_url' => 'https://yourdomain.com/ipn_handler.php'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.coinpayments.net/api.php',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $json = json_decode($response, true);
    if ($json['error'] == 'ok') {
        return $json['result']['url'];
    }
    return false;
}

function verifyIPN($postData) {
    ksort($postData);
    $hmac = hash_hmac('sha512', urldecode(http_build_query($postData)), COINPAYMENTS_IPN_SECRET);
    return $hmac === $postData['ipn_sig'];
}

/**
 * Mining Earnings Calculation
 */
function updateMiningEarnings($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT mp.daily_return, up.current_balance 
        FROM user_plans up
        JOIN mining_plans mp ON up.plan_id = mp.id
        WHERE up.user_id = ? AND up.end_date >= CURDATE()
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $earnings = ($row['current_balance'] * $row['daily_return']) / 100;
        updateBalance($userId, $earnings);
        logTransaction($userId, 'earn', $earnings);
    }
}

function logTransaction($userId, $type, $amount, $txid = null) {
    $conn = connectDB();
    $status = ($type == 'deposit') ? 'completed' : 'pending';
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, txid, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsds", $userId, $type, $amount, $txid, $status);
    $stmt->execute();
}

/**
 * Withdrawal Approval
 */
function approveWithdrawal($transactionId) {
    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
    $stmt->bind_param("i", $transactionId);
    return $stmt->execute();
}
