<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

redirectToLoginIfNotLoggedIn();

$userId = $_SESSION['user_id'];
$referralLink = generateReferralLink($userId);
$referralsCount = getReferralsCount($userId);
$totalCommission = getTotalCommission($userId);

function getReferralsCount($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0];
}

function getTotalCommission($userId) {
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT SUM(bonus_amount) FROM referrals WHERE referrer_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0] ?? 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Referral Program</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="referral-container">
        <h1>Referral Program</h1>
        <p>Earn 15% commission on deposits made by your referrals!</p>
        
        <div class="referral-link-box">
            <p>Your Referral Link:</p>
            <input type="text" value="<?= $referralLink ?>" readonly>
            <button onclick="copyToClipboard()">Copy</button>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <h3>Referrals</h3>
                <p><?= $referralsCount ?></p>
            </div>
            <div class="stat-item">
                <h3>Total Commission</h3>
                <p><?= number_format($totalCommission, 8) ?> DOGE</p>
            </div>
        </div>
    </div>
    
    <script>
        function copyToClipboard() {
            const linkField = document.querySelector('.referral-link-box input');
            linkField.select();
            document.execCommand('copy');
            alert('Referral link copied!');
        }
    </script>
</body>
</html>
