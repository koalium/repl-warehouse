<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "koaliumi_inv_editor"; // Replace with your database username
$password = "koala551364"; // Replace with your database password
$dbname = "koaliumi_inv_db"; // Replace with your database name


// Database credentials
$conn = new mysqli($servername, $username, $password, $dbname);

// Get wallet address
$walletAddress = $_POST['walletAddress'];

// Check if account exists
$stmt = $conn->prepare("SELECT id FROM accounts WHERE wallet_address = ?");
$stmt->bind_param("s", $walletAddress);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Existing user - return user ID
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'userId' => $user['id'],
        'message' => 'Welcome back! Redirecting...'
    ]);
} else {
    // Create new user
    $stmt = $conn->prepare("INSERT INTO accounts (wallet_address) VALUES (?)");
    $stmt->bind_param("s", $walletAddress);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        
        // Create affiliate entry
        $affiliateId = 1000 + $userId;
        $stmt = $conn->prepare("INSERT INTO affiliates (userid, affiliate_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $affiliateId);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'userId' => $userId,
            'message' => 'Registration successful! Redirecting...'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
}


$conn->close();
?>
