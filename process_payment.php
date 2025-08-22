<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$planId = $_POST['planId'];

// Get plan details
$conn = connectDB();
$stmt = $conn->prepare("SELECT price_doge FROM mining_plans WHERE id = ?");
$stmt->bind_param("i", $planId);
$stmt->execute();
$plan = $stmt->get_result()->fetch_assoc();

if (!$plan) {
    echo json_encode(['success' => false, 'error' => 'Plan not found']);
    exit;
}

// Create CoinPayments payment
$paymentUrl = createPayment($userId, $plan['price_doge'], 'DOGE');
if ($paymentUrl) {
    echo json_encode([
        'success' => true,
        'paymentUrl' => $paymentUrl
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Payment creation failed']);
}
