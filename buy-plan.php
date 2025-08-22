<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

redirectToLoginIfNotLoggedIn();

$userId = $_SESSION['user_id'];
$availablePlans = getAvailablePlans();

function getAvailablePlans() {
    $conn = connectDB();
    $result = $conn->query("SELECT * FROM mining_plans WHERE status = 'active'");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Mining Plan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="plans-container">
        <h1>Select a Mining Plan</h1>
        <div class="plans-grid">
            <?php foreach ($availablePlans as $plan): ?>
                <div class="plan-card">
                    <h3><?= $plan['name'] ?></h3>
                    <p>Price: <?= number_format($plan['price_doge'], 8) ?> DOGE</p>
                    <p>Daily Return: <?= $plan['daily_return'] ?>%</p>
                    <p>Duration: <?= $plan['duration_days'] ?> days</p>
                    <button onclick="purchasePlan(<?= $plan['id'] ?>)">
                        Purchase
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        function purchasePlan(planId) {
            // AJAX call to process payment
            fetch('process_payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({planId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.paymentUrl;
                } else {
                    alert('Purchase failed: ' + data.error);
                }
            });
        }
    </script>
</body>
</html>
