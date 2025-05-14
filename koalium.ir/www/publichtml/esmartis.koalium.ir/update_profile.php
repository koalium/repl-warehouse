<?php
// URL: http://localhost/publichtml/smartis.koalium.ir/update_profile.php
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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $showname = $_POST['showname'];
    $bio = $_POST['bio'];
    $address = $_POST['address'];
    $userId = $_SESSION['user_id'];

    // Update profile
    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, showname, bio, address) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE showname = ?, bio = ?, address = ?");
    $stmt->bind_param("issssss", $userId, $showname, $bio, $address, $showname, $bio, $address);
    $stmt->execute();

    echo "Profile updated successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
</head>
<body>
    <h2>Update Profile</h2>
    <form method="POST" action="">
        <input type="text" name="showname" placeholder="Showname" required>
        <textarea name="bio" placeholder="Bio" required></textarea>
        <input type="text" name="address" placeholder="Address" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>