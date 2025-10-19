<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// Get statistics
$stats = [];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// New users this month
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$stats['new_users_month'] = mysqli_fetch_assoc($result)['total'];

// Total crop recommendations
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM district_crop_recommendation");
$stats['total_recommendations'] = mysqli_fetch_assoc($result)['total'];

// Total districts
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT district) as total FROM district_crop_recommendation");
$stats['total_districts'] = mysqli_fetch_assoc($result)['total'];

// Recent user registrations
$recent_users = [];
$result = mysqli_query($conn, "SELECT fullname, email, location, created_at FROM users ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_users[] = $row;
}

// Recent admin activities
$recent_activities = [];
$result = mysqli_query($conn, "SELECT al.action, al.ip_address, al.created_at, a.fullname FROM admin_logs al JOIN admins a ON al.admin_id = a.id ORDER BY al.created_at DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_activities[] = $row;
}

// Add stats for AI Crop Monitor and Crop Advice
$ai_stats = [
    'total_crops' => 0,
    'total_reports' => 0,
    'active_users' => 0
];
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM ai_crop_monitoring");
if ($res) $ai_stats['total_crops'] = mysqli_fetch_assoc($res)['total'];
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_disease_reports");
if ($res) $ai_stats['total_reports'] = mysqli_fetch_assoc($res)['total'];
$res = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM ai_crop_monitoring");
if ($res) $ai_stats['active_users'] = mysqli_fetch_assoc($res)['total'];
$crop_advice_count = 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_master");
if ($res) $crop_advice_count = mysqli_fetch_assoc($res)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Smart Agriculture</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .admin-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header h1 {
            font-size: 24px;
            font-weight: 700;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding: 20px 0;
        }
        .nav-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #666;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .nav-item:hover, .nav-item.active {
            background: #f8f9fa;
            color: #1e3c72;
            border-left-color: #1e3c72;
        }
        .nav-item i {
            width: 20px;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #1e3c72;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .card-header h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        .card-body {
            padding: 20px;
        }
        .user-item, .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        .user-item:last-child, .activity-item:last-child {
            border-bottom: none;
        }
        .user-info h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }
        .user-info p {
            font-size: 12px;
            color: #666;
        }
        .activity-info h4 {
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
        }
        .activity-info p {
            font-size: 11px;
            color: #666;
        }
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div><?= htmlspecialchars($admin_name) ?></div>
                <small><?= htmlspecialchars($admin_role) ?></small>
            </div>
            <a href="admin_logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="admin_dashboard.php" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="admin_users.php" class="nav-item">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="admin_crops.php" class="nav-item">
                <i class="fas fa-seedling"></i> Crop Recommendations
            </a>
            <a href="admin_crop_advice.php" class="nav-item">
                <i class="fas fa-leaf"></i> Crop Advice
            </a>
            <a href="admin_irrigation.php" class="nav-item">
                <i class="fas fa-tint"></i> Irrigation Tips
            </a>
            <a href="admin_prices.php" class="nav-item">
                <i class="fas fa-chart-line"></i> Market Prices
            </a>
            <a href="admin_ai_crop_monitor.php" class="nav-item">
                <i class="fas fa-robot"></i> AI Crop Monitor
            </a>
            <a href="admin_equipment_rental.php" class="nav-item">
                <i class="fas fa-tractor"></i> Agri Equipment
            </a>
            <a href="admin_analytics.php" class="nav-item">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="admin_settings.php" class="nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>

        <div class="main-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?= number_format($stats['total_users']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>New Users This Month</h3>
                    <div class="number"><?= number_format($stats['new_users_month']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Crop Recommendations</h3>
                    <div class="number"><?= number_format($stats['total_recommendations']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Districts Covered</h3>
                    <div class="number"><?= number_format($stats['total_districts']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>AI Crop Monitor</h3>
                    <div class="number"><?= number_format($ai_stats['total_crops']) ?> crops<br><?= number_format($ai_stats['total_reports']) ?> reports<br><?= number_format($ai_stats['active_users']) ?> users</div>
                </div>
                <div class="stat-card">
                    <h3>Crop Advice</h3>
                    <div class="number"><?= number_format($crop_advice_count) ?> crops</div>
                </div>
            </div>

            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-users"></i> Recent User Registrations</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_users)): ?>
                            <p style="color: #666; text-align: center;">No recent registrations</p>
                        <?php else: ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="user-item">
                                    <div class="user-info">
                                        <h4><?= htmlspecialchars($user['fullname']) ?></h4>
                                        <p><?= htmlspecialchars($user['email']) ?> • <?= htmlspecialchars($user['location']) ?></p>
                                    </div>
                                    <small style="color: #666;"><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Recent Activities</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_activities)): ?>
                            <p style="color: #666; text-align: center;">No recent activities</p>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4><?= htmlspecialchars($activity['action']) ?></h4>
                                        <p><?= htmlspecialchars($activity['fullname']) ?> • <?= htmlspecialchars($activity['ip_address']) ?></p>
                                    </div>
                                    <small style="color: #666;"><?= date('M j, H:i', strtotime($activity['created_at'])) ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> User Registration Trend</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="userChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User registration chart
        const ctx = document.getElementById('userChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Users',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: '#1e3c72',
                    backgroundColor: 'rgba(30, 60, 114, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f3f4'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 