<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['plan_id'])) {
    http_response_code(400);
    exit(json_encode(["error" => "Missing plan_id parameter"]));
}

$planId = intval($input['plan_id']);

// Get plan details
$conn = connectDB();
$stmt = $conn->prepare("SELECT price_doge FROM mining_plans WHERE id = ?");
$stmt->bind_param("i", $planId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit(json_encode(["error" => "Plan not found"]));
}

$plan = $result->fetch_assoc();
$priceDoge = $plan['price_doge'];

// Create payment
$paymentUrl = createPayment(null, $priceDoge, 'DOGE');

if ($paymentUrl) {
    echo json_encode(["payment_url" => $paymentUrl]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to create payment"]);
}
