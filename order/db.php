<?php
// db.php
$host = 'localhost';
$dbname = 'furnihome'; // Updated to your actual database name
$username = 'root'; // Change this if your local server uses a different username
$password = ''; // Add your password if your local server requires one

try {
    // Create the dbo connection using the furnihome database
    $dbo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set error mode to exception to help catch mistakes easily
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>