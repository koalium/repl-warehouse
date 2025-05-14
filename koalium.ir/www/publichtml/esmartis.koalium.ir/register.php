<?php
// URL: http://smartis.koalium.ir/register.php
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

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $captcha = $_POST['captcha'];

    // Check if user is blocked due to CAPTCHA attempts
    if (isset($_SESSION['captcha_attempts']) && $_SESSION['captcha_attempts'] >= 3) {
        $blockTime = $_SESSION['captcha_block_time'];
        if (time() < $blockTime) {
            $remainingTime = $blockTime - time();
            echo "You are blocked for $remainingTime seconds due to too many CAPTCHA failures.";
            exit;
        } else {
            // Unblock user after 2 minutes
            unset($_SESSION['captcha_attempts']);
            unset($_SESSION['captcha_block_time']);
        }
    }

    // Validate CAPTCHA
    if ($captcha !== $_SESSION['captcha_code']) {
        $_SESSION['captcha_attempts'] = ($_SESSION['captcha_attempts'] ?? 0) + 1;

        if ($_SESSION['captcha_attempts'] >= 3) {
            $_SESSION['captcha_block_time'] = time() + 120; // Block for 2 minutes
            echo "Too many CAPTCHA failures. You are blocked for 2 minutes.";
            exit;
        }

        echo "Invalid CAPTCHA. Please try again.";
        exit;
    }

    // Reset CAPTCHA attempts on success
    unset($_SESSION['captcha_attempts']);
    unset($_SESSION['captcha_block_time']);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Email already exists.";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashedPassword);
    $stmt->execute();

    // Get the user ID
    $userId = $stmt->insert_id;

    // Generate verification token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

    // Store token in database
    $stmt = $conn->prepare("INSERT INTO verification_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $token, $expiresAt);
    $stmt->execute();

    // Send verification email
    $verificationUrl = "http://smartis.koalium.ir/verify.php?token=$token";
    $subject = "Verify Your Email";
    $message = "Click the link to verify your email: $verificationUrl";
    $headers = "From: no-reply@smartis.koalium.ir";

    if (mail($email, $subject, $message, $headers)) {
        echo "Registration successful. Please check your email to verify your account.";
    } else {
        echo "Failed to send verification email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function refreshCaptcha() {
            const captchaImage = document.getElementById('captcha-image');
            captchaImage.src = 'captcha.php?' + new Date().getTime(); // Force refresh
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="captcha">
                <img id="captcha-image" src="captcha.php" alt="CAPTCHA">
                <button type="button" onclick="refreshCaptcha()" class="btn-refresh">Refresh</button>
                <input type="text" name="captcha" placeholder="Enter CAPTCHA" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>