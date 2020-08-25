<?php
// this vars should be changed as needed 
$servername = "localhost";        // MySQL server address
$username   = "es_admin";         // user for connecting to database
$password   = "oOWCN58udB24oGg8"; // password
$dbname     = "euro_spisok";      // database name

try {
	$conn = new PDO("mysql:host=$servername;charset=utf8mb4;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo "Connection failed: " . $e->getMessage();
}

return $conn;
