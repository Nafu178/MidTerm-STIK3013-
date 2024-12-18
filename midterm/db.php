<?php
// db.php: Database connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myevent";
$port = 3307;

try {
    // Create a new PDO connection with port specified
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
