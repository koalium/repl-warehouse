<?php
// URL: http://smartis.koalium.ir/add_service.php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "koaliumi_editor";
$password = "koala551364";
$dbname = "koaliumi_smartis_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle service addition
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['service_id'])) {
    $serviceId = $_GET['service_id'];
    $userId = $_SESSION['user_id'];

    // Fetch service details
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $service = $stmt->get_result()->fetch_assoc();

    // Generate payment ticket and wallet address
    $paymentTicket = bin2hex(random_bytes(16));
    $walletAddress = "BTC20_WALLET_ADDRESS"; // Replace with actual wallet address

    // Insert into user_services table
    $stmt = $conn->prepare("INSERT INTO user_services (user_id, service_id, payment_ticket, wallet_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userId, $serviceId, $paymentTicket, $walletAddress);
    $stmt->execute();

    echo "Service added successfully. Please complete the payment.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Add Service</h2>
        <p>Please send the payment to the following BTC20 wallet address:</p>
        <p><strong>Wallet Address:</strong> <?php echo $walletAddress; ?></p>
        <p><strong>Payment Ticket:</strong> <?php echo $paymentTicket; ?></p>
        <p>Once the payment is confirmed, the service will be activated.</p>
    </div>
</body>
</html>