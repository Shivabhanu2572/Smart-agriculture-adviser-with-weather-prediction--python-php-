<?php
$servername = "localhost";       // usually localhost in XAMPP
$username = "root";              // default username in phpMyAdmin
$password = "";                  // empty by default for root
$database = "smart_agri";        // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
?>
