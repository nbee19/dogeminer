<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin-auth.php';

redirectToAdminLoginIfNotLoggedIn();

// Get statistics
$totalUsers = getTotalUsers();
$totalDeposits = getTotalDeposits();
$totalWithdrawals = getTotalWithdrawals();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="admin-dashboard">
        <h1>Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h2>Total Users</h2>
                <p><?= $totalUsers ?></p>
            </div>
            <div class="stat-card">
                <h2>Total Deposits</h2>
                <p><?= number_format($totalDeposits, 2) ?> DOGE</p>
            </div>
            <div class="stat-card">
                <h2>Total Withdrawals</h2>
                <p><?= number_format($totalWithdrawals, 2) ?> DOGE</p>
            </div>
        </div>
        
        <nav>
            <a href="users.php">Manage Users</a>
            <a href="transactions.php">View Transactions</a>
            <a href="plans.php">Manage Plans</a>
            <a href="settings.php">Global Settings</a>
        </nav>
    </div>
</body>
</html>
