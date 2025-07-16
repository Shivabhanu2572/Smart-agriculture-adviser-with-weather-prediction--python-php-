<?php
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
