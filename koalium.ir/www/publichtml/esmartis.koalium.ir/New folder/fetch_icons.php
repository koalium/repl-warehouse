<?php
// Database credentials
$servername = "localhost"; // Replace with your server name
$username = "koaliumi_editor"; // Database username
$password = "koala551364"; // Database password
$dbname = "koaliumi_smartis_db"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch icon URLs from the `pageimages` table
$sql = "SELECT url FROM pageimages";
$result = $conn->query($sql);

$iconPaths = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $iconPaths[] = $row['url']; // Store each URL in an array
    }
} else {
    echo "No icons found in the database.";
}

// Close the connection
$conn->close();

// Return the icon paths as JSON
header('Content-Type: application/json');
echo json_encode($iconPaths);
?>