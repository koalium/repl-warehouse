<?php
// URL: http://localhost/publichtml/smartis.koalium.ir/verify.php
session_start();

// Database connection
$servername = "localhost";
$username = "koaliumi_editor";
$password = "koala551364";
$dbname = "koaliumi_smartis_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle token verification
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT user_id FROM verification_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userId = $row['user_id'];

        // Mark user as verified
        $stmt = $conn->prepare("UPDATE users SET verified = 1 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        // Delete the token
        $stmt = $conn->prepare("DELETE FROM verification_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "Email verified successfully. You can now log in.";
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>