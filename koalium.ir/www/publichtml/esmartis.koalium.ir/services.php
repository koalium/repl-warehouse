<?php
// URL: http://smartis.koalium.ir/services.php
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

// Fetch all services
$stmt = $conn->prepare("SELECT * FROM services");
$stmt->execute();
$services = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let currentIndex = 0;
        const services = <?php echo json_encode($services->fetch_all(MYSQLI_ASSOC)); ?>;

        function rotateServices() {
            const serviceBlocks = document.querySelectorAll('.service-block');
            serviceBlocks.forEach((block, index) => {
                block.style.display = (index >= currentIndex && index < currentIndex + 3) ? 'block' : 'none';
            });
            currentIndex = (currentIndex + 3) % services.length;
        }

        setInterval(rotateServices, 20000); // Rotate every 20 seconds
    </script>
</head>
<body>
    <div class="container">
        <h2>Available Services</h2>
        <div class="services">
            <?php while ($service = $services->fetch_assoc()): ?>
                <div class="service-block">
                    <h3><?php echo $service['name']; ?></h3>
                    <img src="<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>">
                    <p><?php echo $service['description']; ?></p>
                    <p>Price: $<?php echo $service['price']; ?></p>
                    <p>Discount: <?php echo $service['discount']; ?>%</p>
                    <button onclick="addService(<?php echo $service['id']; ?>)" class="btn">Add Service</button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script>
        function addService(serviceId) {
            // Redirect to payment page or handle service addition
            window.location.href = `add_service.php?service_id=${serviceId}`;
        }
    </script>
</body>
</html>