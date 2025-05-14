<?php
// URL: http://smartis.koalium.ir/dashboard.php
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

// Fetch user info from users table
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user profile info from user_profiles table
$stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Handle user info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $backupEmail = $_POST['backup_email'];
    $phoneNumber = $_POST['phone_number'];
    $secondPhone = $_POST['second_phone'];
    $partnerAccount = $_POST['partner_account'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $service = $_POST['service'];
    $packageId = $_POST['package_id'];

    // Update user profile in the database
    $stmt = $conn->prepare("UPDATE user_profiles SET backup_email = ?, phone_number = ?, second_phone = ?, partner_account = ?, address1 = ?, address2 = ?, service = ?, package_id = ? WHERE user_id = ?");
    $stmt->bind_param("sssssssii", $backupEmail, $phoneNumber, $secondPhone, $partnerAccount, $address1, $address2, $service, $packageId, $userId);
    $stmt->execute();

    // Refresh profile data
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();

    echo "<p>Profile updated successfully.</p>";
}

// Fetch user services
$stmt = $conn->prepare("SELECT s.name, s.description, s.price, us.status FROM user_services us JOIN services s ON us.service_id = s.id WHERE us.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userServices = $stmt->get_result();

// Fetch all available services
$stmt = $conn->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->get_result();

// Handle service addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $serviceId = $_POST['service_id'];

    // Generate payment ticket and wallet address
    $paymentTicket = bin2hex(random_bytes(16));
    $walletAddress = "BTC20_WALLET_ADDRESS"; // Replace with actual wallet address

    // Insert into user_services table
    $stmt = $conn->prepare("INSERT INTO user_services (user_id, service_id, payment_ticket, wallet_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userId, $serviceId, $paymentTicket, $walletAddress);
    $stmt->execute();

    echo "<p>Service added successfully. Please complete the payment.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Your Dashboard</h1>

        <!-- Block 1: User Specifications -->
        <div class="block">
            <h2>Your Specifications</h2>
            <form method="POST" action="">
                <label for="username">Username:</label>
                <input type="text" name="username" value="<?php echo $user['username']; ?>" readonly>

                <label for="email">Email:</label>
                <input type="email" name="email" value="<?php echo $user['email']; ?>" readonly>

                <label for="backup_email">Backup Email:</label>
                <input type="email" name="backup_email" value="<?php echo $profile['backup_email']; ?>">

                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" value="<?php echo $profile['phone_number']; ?>">

                <label for="second_phone">Second Phone:</label>
                <input type="text" name="second_phone" value="<?php echo $profile['second_phone']; ?>">

                <label for="partner_account">Partner Account:</label>
                <input type="text" name="partner_account" value="<?php echo $profile['partner_account']; ?>">

                <label for="address1">Address 1:</label>
                <input type="text" name="address1" value="<?php echo $profile['address1']; ?>">

                <label for="address2">Address 2:</label>
                <input type="text" name="address2" value="<?php echo $profile['address2']; ?>">

                <label for="service">Service:</label>
                <input type="text" name="service" value="<?php echo $profile['service']; ?>">

                <label for="package_id">Package ID:</label>
                <input type="number" name="package_id" value="<?php echo $profile['package_id']; ?>">

                <button type="submit" name="update_info" class="btn">Update Info</button>
            </form>
        </div>

        <!-- Block 2: User Services -->
        <div class="block">
            <h2>Your Services</h2>
            <?php if ($userServices->num_rows > 0): ?>
                <ul>
                    <?php while ($service = $userServices->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo $service['name']; ?></strong><br>
                            <?php echo $service['description']; ?><br>
                            Price: $<?php echo $service['price']; ?><br>
                            Status: <?php echo $service['status']; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No services found.</p>
            <?php endif; ?>
        </div>

        <!-- Block 3: Add Services -->
        <div class="block">
            <h2>Add Services</h2>
            <form method="POST" action="">
                <select name="service_id" required>
                    <?php while ($service = $services->fetch_assoc()): ?>
                        <option value="<?php echo $service['id']; ?>">
                            <?php echo $service['name']; ?> - $<?php echo $service['price']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add_service" class="btn">Add Service</button>
            </form>
        </div>

        <!-- Block 4: Payment Status -->
        <div class="block">
            <h2>Payment Status</h2>
            <?php
            // Fetch pending services
            $stmt = $conn->prepare("SELECT s.name, us.payment_ticket, us.wallet_address FROM user_services us JOIN services s ON us.service_id = s.id WHERE us.user_id = ? AND us.status = 'pending'");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $pendingServices = $stmt->get_result();

            if ($pendingServices->num_rows > 0): ?>
                <ul>
                    <?php while ($service = $pendingServices->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo $service['name']; ?></strong><br>
                            Payment Ticket: <?php echo $service['payment_ticket']; ?><br>
                            Wallet Address: <?php echo $service['wallet_address']; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No pending payments.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>