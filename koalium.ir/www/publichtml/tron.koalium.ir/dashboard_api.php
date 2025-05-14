<?php
header('Content-Type: application/json');
require 'config.php';

$userId = $_GET['userId'] ?? json_decode(file_get_contents('php://input'), true)['userId'];

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['action'] === 'submitTransaction') {
        $txHash = $data['txHash'];
        // Insert into transactions table
        $conn->query("INSERT INTO transactions (txhash) VALUES ('$txHash')");
        $txId = $conn->insert_id;
        $conn->query("INSERT INTO user_transactions (userid, txid) VALUES ($userId, $txId)");
        echo json_encode(['success' => true]);
    }
} else {
    // Get dashboard data
    $result = $conn->query("SELECT * FROM affiliates WHERE userid = $userId");
    $affiliate = $result->fetch_assoc();
    
    $result = $conn->query("
        SELECT t.txhash, tc.amount, tc.confirmation AS status 
        FROM user_transactions ut
        JOIN transactions t ON ut.txid = t.id
        LEFT JOIN transaction_confirmed tc ON t.txhash = tc.txhash
        WHERE ut.userid = $userId
    ");
    
    echo json_encode([
        'affiliateId' => $affiliate['affiliate_id'],
        'earnings' => $affiliate['earnings'],
        'transactions' => $result->fetch_all(MYSQLI_ASSOC)
    ]);
}
?>