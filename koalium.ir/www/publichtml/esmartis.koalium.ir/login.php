<?php
// URL: http://smartis.koalium.ir/login.php
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

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user is blocked due to password attempts
    if (isset($_SESSION['password_attempts']) && $_SESSION['password_attempts'] >= 3) {
        $blockTime = $_SESSION['password_block_time'];
        if (time() < $blockTime) {
            $remainingTime = $blockTime - time();
            echo "You are blocked for $remainingTime seconds due to too many password failures.";
            exit;
        } else {
            // Unblock user after 5 minutes
            unset($_SESSION['password_attempts']);
            unset($_SESSION['password_block_time']);
        }
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, password, verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            if ($user['verified']) {
                // Start session
                $_SESSION['user_id'] = $user['id'];
                header("Location: dashboard.php");
                exit;
            } else {
                echo "Please verify your email first.";
            }
        } else {
            $_SESSION['password_attempts'] = ($_SESSION['password_attempts'] ?? 0) + 1;

            if ($_SESSION['password_attempts'] >= 3) {
                $_SESSION['password_block_time'] = time() + 300; // Block for 5 minutes
                echo "Too many password failures. You are blocked for 5 minutes.";
                exit;
            }

            echo "Invalid password.";
        }
    } else {
        echo "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>