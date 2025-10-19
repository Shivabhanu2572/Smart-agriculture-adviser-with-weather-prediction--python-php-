<?php
session_start();

// Log the logout action if admin is logged in
if (isset($_SESSION['admin_id'])) {
    include("db_connection.php");
    $admin_id = $_SESSION['admin_id'];
    $logout_time = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    mysqli_query($conn, "INSERT INTO admin_logs (admin_id, action, ip_address, created_at) VALUES ($admin_id, 'Logout', '$ip_address', '$logout_time')");
}

// Destroy all session data
session_destroy();

// Redirect to admin login
header("Location: admin_login.php");
exit();
?> 