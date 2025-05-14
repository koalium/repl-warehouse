<?php
// config.php
$servername = "localhost";
$username = "koaliumi_inv_editor";
$password = "koala551364";
$dbname = "koaliumi_inv_db";

$conn = new mysqli($servername, $username, $password, $dbname);
// ... error handling ...

// dashboard_handler.php
header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $userId = $_POST['userId'];

    switch ($action) {
        case 'add_transaction':
            $txhash = $conn->real_escape_string($_POST['txhash']);
            // Insert into transactions
            $conn->query("INSERT INTO transactions (txhash) VALUES ('$txhash')");
            $txId = $conn->insert_id;
            // Link to user
            $conn->query("INSERT INTO user_transactions (userid, txid) VALUES ($userId, $txId)");
            echo json_encode(['success' => true]);
            break;

        case 'get_confirmed':
            $result = $conn->query("SELECT * FROM transaction_confirmed WHERE userid = $userId");
            echo json_encode(['data' => $result->fetch_all(MYSQLI_ASSOC)]);
            break;

        case 'get_affiliate':
            $result = $conn->query("SELECT * FROM affiliates WHERE userid = $userId");
            echo json_encode($result->fetch_assoc());
            break;
    }
}

// Background checker (run as cron job every 30 seconds)
function checkTransactions() {
    require 'config.php';
    
    // Get unconfirmed transactions
    $result = $conn->query("SELECT * FROM user_transactions ut 
                          JOIN transactions t ON ut.txid = t.id
                          LEFT JOIN transaction_confirmed tc ON t.txhash = tc.txhash
                          WHERE tc.id IS NULL");
    
    while ($row = $result->fetch_assoc()) {
        // Check Tronscan API (pseudo-code)
        $txData = file_get_contents("https://apilist.tronscan.org/api/transaction-info?hash=".$row['txhash']);
        $txData = json_decode($txData, true);
        
        if ($txData['confirmed']) {
            // Check if receiver is in wallet_inv table
            $receiver = $txData['to'];
            $valid = $conn->query("SELECT id FROM wallet_inv WHERE address = '$receiver'")->num_rows > 0;
            
            if ($valid) {
                $conn->query("INSERT INTO transaction_confirmed 
                            (userid, txhash, amount, time, confirmation)
                            VALUES ({$row['userid']}, '{$row['txhash']}', 
                                    {$txData['amount']}, NOW(), 1)");
                
                // Handle affiliate earnings
                $affiliate = $conn->query("SELECT * FROM affiliates WHERE userid = {$row['userid']}");
                if ($affiliate->num_rows > 0) {
                    $conn->query("UPDATE affiliates SET earnings = earnings + ({$txData['amount']} * 0.1) 
                                  WHERE userid = {$row['userid']}");
                }
            }
        }
    }
}