<?php
require_once 'config.php';

function createPayment($userId, $amountUSD, $currency = 'USD') {
    $ch = curl_init();
    $postData = [
        'key' => COINPAYMENTS_API_KEY,
        'version' => 1,
        'cmd' => 'create_transaction',
        'currency1' => $currency,
        'currency2' => 'DOGE',
        'amount' => $amountUSD,
        'buyer_email' => 'user@example.com', // Replace with actual user email
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
?>
