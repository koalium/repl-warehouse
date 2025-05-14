<?php
require_once '../api/config.php';
require_once '../api/utils/JwtHandler.php';

// Check authentication
if(!isset($_COOKIE['token'])) {
    //header('Location: index.html');
  //  exit();
}

// Validate JWT
try {
    $decoded = JwtHandler::validateToken($_COOKIE['token']);
} catch(Exception $e) {
    //header('Location: index.html');
   // exit();
}

// Fetch initial data from API
$wallet = $decoded->wallet;
$api_url = '../api/';

// Function to call API
function callApi($url, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Get user data
$userData = callApi($api_url . 'user', $_COOKIE['token']);
$transactions = callApi($api_url . 'transactions', $_COOKIE['token']);
$contracts = callApi($api_url . 'contracts', $_COOKIE['token']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Add your dashboard styles here */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            color: white;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">Updating Data...</div>
    
    <!-- Dashboard Content -->
    <div id="dashboard">
        <!-- Wallet Info -->
        <div class="wallet-info">
            <h2>Wallet: <?php echo htmlspecialchars($userData['wallet']); ?></h2>
            <p>Registered: <?php echo $userData['registration_date']; ?></p>
            <p>Total Invested: <?php echo $userData['total_invested']; ?> TRX</p>
            <p>Total Profit: <?php echo $userData['total_profit']; ?> TRX</p>
        </div>

        <!-- Active Contracts -->
        <div class="contracts" id="contracts">
            <?php foreach($contracts as $contract): ?>
            <div class="contract">
                <h3><?php echo htmlspecialchars($contract['name']); ?></h3>
                <p><?php echo htmlspecialchars($contract['description']); ?></p>
                <p>Profit: <?php echo $contract['profit']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Transactions -->
        <div class="transactions" id="transactions">
            <h3>Recent Transactions</h3>
            <?php foreach($transactions as $tx): ?>
            <div class="transaction">
                <p>Amount: <?php echo $tx['amount']; ?> TRX</p>
                <p>Status: <?php echo $tx['status']; ?></p>
                <p>Date: <?php echo $tx['time']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Auto-refresh data every 30 seconds
        let refreshInterval = setInterval(updateData, 30000);

        async function updateData() {
            showLoading();
            
            try {
                // Fetch updated data
                const [userData, transactions, contracts] = await Promise.all([
                    fetchData('user'),
                    fetchData('transactions'),
                    fetchData('contracts')
                ]);

                // Update wallet info
                document.querySelector('.wallet-info').innerHTML = `
                    <h2>Wallet: ${userData.wallet}</h2>
                    <p>Registered: ${userData.registration_date}</p>
                    <p>Total Invested: ${userData.total_invested} TRX</p>
                    <p>Total Profit: ${userData.total_profit} TRX</p>
                `;

                // Update contracts
                const contractsContainer = document.getElementById('contracts');
                contractsContainer.innerHTML = contracts.map(contract => `
                    <div class="contract">
                        <h3>${contract.name}</h3>
                        <p>${contract.description}</p>
                        <p>Profit: ${contract.profit}</p>
                    </div>
                `).join('');

                // Update transactions
                const txContainer = document.getElementById('transactions');
                txContainer.innerHTML = transactions.map(tx => `
                    <div class="transaction">
                        <p>Amount: ${tx.amount} TRX</p>
                        <p>Status: ${tx.status}</p>
                        <p>Date: ${tx.time}</p>
                    </div>
                `).join('');

            } catch(error) {
                console.error('Update failed:', error);
            } finally {
                hideLoading();
            }
        }

        async function fetchData(endpoint) {
            const response = await fetch(`/api/${endpoint}`, {
                headers: {
                    'Authorization': `Bearer ${getCookie('token')}`
                }
            });
            
            if(!response.ok) throw new Error('Network error');
            return response.json();
        }

        function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
    </script>
</body>
</html>