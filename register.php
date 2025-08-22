<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dogecoinAddress = sanitizeInput($_POST['dogecoin_address']);
    $referrerId = null;
    
    if (isset($_GET['ref'])) {
        $referrerId = base64_decode($_GET['ref']);
    }
    
    if (!validateDogecoinAddress($dogecoinAddress)) {
        $error = "Invalid Dogecoin address";
    } else {
        $conn = connectDB();
        $stmt = $conn->prepare("INSERT INTO users (dogecoin_address, referrer_id) VALUES (?, ?)");
        $stmt->bind_param("si", $dogecoinAddress, $referrerId);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Registration failed. Try again.";
        }
    }
}
?>
<!-- HTML form with error display -->
