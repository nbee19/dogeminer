<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

redirectToLoginIfNotLoggedIn();

$userId = $_SESSION['user_id'];
$balance = getCurrentBalance($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    
    if ($amount < MIN_WITHDRAWAL) {
        $error = "Minimum withdrawal is " . MIN_WITHDRAWAL . " DOGE";
    } elseif ($amount > $balance) {
        $error = "Insufficient balance";
    } else {
        // Process withdrawal
        updateBalance($userId, $amount, 'debit');
        logTransaction($userId, 'withdrawal', $amount);
        $success = "Withdrawal request submitted successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdraw Funds</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="withdraw-container">
        <h1>Withdraw Dogecoin</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label>Amount (DOGE):</label>
            <input type="number" name="amount" step="0.00000001" min="<?= MIN_WITHDRAWAL ?>" max="<?= $balance ?>" required>
            <p>Available Balance: <?= number_format($balance, 8) ?> DOGE</p>
            <button type="submit">Submit Withdrawal Request</button>
        </form>
    </div>
</body>
</html>
