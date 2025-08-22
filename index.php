<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Fetch recent transactions for display
$recentTransactions = [];
try {
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT t.type, t.amount, t.created_at, u.dogecoin_address
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error silently for production
    error_log("Error fetching recent transactions: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Cloud Mining Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="hero-section">
        <div class="container">
            <h1>Welcome to Crypto Cloud Mining</h1>
            <p>Mine Dogecoin effortlessly in the cloud. Start earning today!</p>
            
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary">Get Started - Register</a>
                <a href="login.php" class="btn btn-secondary">Already have an account? Login</a>
            </div>
        </div>
    </div>

    <div class="recent-transactions-section">
        <div class="container">
            <h2>Recent Transactions</h2>
            <div class="transactions-list">
                <?php if (!empty($recentTransactions)): ?>
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="transaction-item">
                            <div class="transaction-type"><?= ucfirst($transaction['type']) ?></div>
                            <div class="transaction-amount"><?= number_format($transaction['amount'], 8) ?> DOGE</div>
                            <div class="transaction-address"><?= substr($transaction['dogecoin_address'], 0, 20) ?>...</div>
                            <div class="transaction-time"><?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No recent transactions available at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="features-section">
        <div class="container">
            <h2>Why Choose Our Platform?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="icon">⚡</div>
                    <h3>Fast Mining</h3>
                    <p>Start earning immediately after purchasing a mining package.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">💰</div>
                    <h3>High Returns</h3>
                    <p>Potential daily returns of up to 5% depending on your chosen plan.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">🌐</div>
                    <h3>Global Access</h3>
                    <p>Access your mining dashboard from anywhere in the world.</p>
                </div>
                <div class="feature-card">
                    <div class="icon">📱</div>
                    <h3>Mobile Friendly</h3>
                    <p>Fully responsive design works perfectly on all devices.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Sign up using your Dogecoin wallet address.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Choose a Plan</h3>
                    <p>Select from our free or premium mining packages.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Start Mining</h3>
                    <p>Watch your balance grow automatically every day.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Withdraw Earnings</h3>
                    <p>Transfer your Dogecoin to your wallet when ready.</p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>We're dedicated to providing secure and efficient cloud mining services for Dogecoin enthusiasts worldwide.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: support@cryptomining.com</p>
                    <p>Telegram: @CryptoMiningSupport</p>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?= date('Y') ?> Crypto Cloud Mining Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
