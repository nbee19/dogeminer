<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

redirectToLoginIfNotLoggedIn();

$userId = $_SESSION['user_id'];
$currentBalance = getCurrentBalance($userId);

// Update mining earnings (should run via cron)
updateMiningEarnings($userId);

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mining Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
    <div class="dashboard">
        <h1>Mining Dashboard</h1>
        <div class="balance-display">
            <span>Balance: <?= number_format($currentBalance, 8) ?> DOGE</span>
        </div>
        
        <div class="miner-animation">
            <img src="assets/images/miner.gif" alt="Mining Character">
            <div class="progress-bar">
                <div class="progress-fill" style="width: 75%;"></div>
            </div>
        </div>
        
        <div class="plans-section">
            <h2>Active Mining Plans</h2>
            <!-- Display active plans from user_plans table -->
        </div>
        
        <nav>
            <a href="buy-plan.php">Buy Plan</a>
            <a href="withdraw.php">Withdraw</a>
            <a href="referral.php">Referral</a>
        </nav>
    </div>
</body>
</html>
