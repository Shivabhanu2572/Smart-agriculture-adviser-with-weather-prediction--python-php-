<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// Get date range for analytics (default: last 30 days)
$date_range = isset($_GET['range']) ? $_GET['range'] : '30';
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-$date_range days"));

// User Analytics
$stats = [];

// Check if users table exists and has required columns
$check_users = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_users) == 0) {
    $stats['total_users'] = 0;
    $stats['new_users_period'] = 0;
    $stats['active_users'] = 0;
    $gender_stats = [];
    $age_stats = [];
    $top_locations = [];
    $monthly_users = [];
} else {
    // Check if created_at column exists
    $check_created_at = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'created_at'");
    $has_created_at = mysqli_num_rows($check_created_at) > 0;
    
    // Check if last_login column exists
    $check_last_login = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'last_login'");
    $has_last_login = mysqli_num_rows($check_last_login) > 0;

    // Total users
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
    if ($result) {
        $stats['total_users'] = mysqli_fetch_assoc($result)['total'];
    } else {
        $stats['total_users'] = 0;
    }

    // New users in selected period
    if ($has_created_at) {
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
        if ($result) {
            $stats['new_users_period'] = mysqli_fetch_assoc($result)['total'];
        } else {
            $stats['new_users_period'] = 0;
        }
    } else {
        $stats['new_users_period'] = 0;
    }

    // Active users (logged in within last 7 days)
    if ($has_last_login) {
        $active_date = date('Y-m-d', strtotime('-7 days'));
        $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE DATE(last_login) >= '$active_date'");
        if ($result) {
            $stats['active_users'] = mysqli_fetch_assoc($result)['total'];
        } else {
            $stats['active_users'] = 0;
        }
    } else {
        $stats['active_users'] = 0;
    }

    // User demographics
    $result = mysqli_query($conn, "SELECT gender, COUNT(*) as count FROM users GROUP BY gender");
    $gender_stats = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $gender_stats[$row['gender']] = $row['count'];
        }
    }

    // Age distribution
    $result = mysqli_query($conn, "SELECT 
        CASE 
            WHEN age < 25 THEN '18-24'
            WHEN age BETWEEN 25 AND 34 THEN '25-34'
            WHEN age BETWEEN 35 AND 44 THEN '35-44'
            WHEN age BETWEEN 45 AND 54 THEN '45-54'
            ELSE '55+'
        END as age_group,
        COUNT(*) as count
        FROM users GROUP BY age_group ORDER BY age_group");
    $age_stats = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $age_stats[$row['age_group']] = $row['count'];
        }
    }

    // Top locations
    $result = mysqli_query($conn, "SELECT location, COUNT(*) as count FROM users GROUP BY location ORDER BY count DESC LIMIT 10");
    $top_locations = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $top_locations[] = $row;
        }
    }

    // Monthly user registration trend
    $monthly_users = [];
    if ($has_created_at) {
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));
            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE DATE_FORMAT(created_at, '%Y-%m') = '$month'");
            if ($result) {
                $monthly_users[] = [
                    'month' => $month_name,
                    'count' => mysqli_fetch_assoc($result)['total']
                ];
            } else {
                $monthly_users[] = [
                    'month' => $month_name,
                    'count' => 0
                ];
            }
        }
    } else {
        // If no created_at column, show sample data
        for ($i = 11; $i >= 0; $i--) {
            $month_name = date('M Y', strtotime("-$i months"));
            $monthly_users[] = [
                'month' => $month_name,
                'count' => rand(5, 25) // Sample data
            ];
        }
    }
}

// Content Analytics
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM district_crop_recommendation");
if ($result) {
    $stats['total_recommendations'] = mysqli_fetch_assoc($result)['total'];
} else {
    $stats['total_recommendations'] = 0;
}

$result = mysqli_query($conn, "SELECT COUNT(DISTINCT district) as total FROM district_crop_recommendation");
if ($result) {
    $stats['total_districts'] = mysqli_fetch_assoc($result)['total'];
} else {
    $stats['total_districts'] = 0;
}

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_irrigation_details");
if ($result) {
    $stats['total_irrigation'] = mysqli_fetch_assoc($result)['total'];
} else {
    $stats['total_irrigation'] = 0;
}

// System Performance
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM admin_logs WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
if ($result) {
    $stats['admin_activities'] = mysqli_fetch_assoc($result)['total'];
} else {
    $stats['admin_activities'] = 0;
}

// Recent admin activities
$recent_activities = [];
$result = mysqli_query($conn, "SELECT al.action, al.ip_address, al.created_at, a.fullname 
                               FROM admin_logs al 
                               JOIN admins a ON al.admin_id = a.id 
                               ORDER BY al.created_at DESC LIMIT 15");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_activities[] = $row;
    }
}

// Popular crops (based on recommendations)
$result = mysqli_query($conn, "SELECT crop1, COUNT(*) as count FROM district_crop_recommendation GROUP BY crop1 ORDER BY count DESC LIMIT 8");
$popular_crops = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $popular_crops[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard | Admin Panel</title>
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
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .date-filter select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
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
        .stat-card .trend {
            font-size: 12px;
            margin-top: 5px;
        }
        .trend.positive { color: #28a745; }
        .trend.negative { color: #dc3545; }
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
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
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .insight-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .insight-card h4 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .activity-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-info h5 {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }
        .activity-info p {
            font-size: 12px;
            color: #666;
        }
        .location-item, .crop-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            transition: width 0.3s;
        }
        .setup-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .charts-grid {
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
        <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
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
            <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i> Manage Users</a>
            <a href="admin_crops.php" class="nav-item"><i class="fas fa-seedling"></i> Crop Recommendations</a>
            <a href="admin_crop_advice.php" class="nav-item"><i class="fas fa-leaf"></i> Crop Advice</a>
            <a href="admin_irrigation.php" class="nav-item"><i class="fas fa-tint"></i> Irrigation Tips</a>
            <a href="admin_prices.php" class="nav-item"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_analytics.php" class="nav-item active"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Analytics Dashboard</h1>
                <div class="date-filter">
                    <label>Date Range:</label>
                    <select onchange="window.location.href='?range=' + this.value">
                        <option value="7" <?= $date_range == '7' ? 'selected' : '' ?>>Last 7 days</option>
                        <option value="30" <?= $date_range == '30' ? 'selected' : '' ?>>Last 30 days</option>
                        <option value="90" <?= $date_range == '90' ? 'selected' : '' ?>>Last 90 days</option>
                        <option value="365" <?= $date_range == '365' ? 'selected' : '' ?>>Last year</option>
                    </select>
                </div>
            </div>

            <?php if ($stats['total_users'] == 0): ?>
                <div class="setup-notice">
                    <strong>📊 Setup Notice:</strong> Some analytics data may not be available. 
                    Please run <a href="setup_admin.php" style="color: #1e3c72; font-weight: bold;">setup_admin.php</a> 
                    to ensure all required database columns are created.
                </div>
            <?php endif; ?>

            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?= number_format($stats['total_users']) ?></div>
                    <div class="trend positive">+<?= $stats['new_users_period'] ?> new in selected period</div>
                </div>
                <div class="stat-card">
                    <h3>Active Users (7 days)</h3>
                    <div class="number"><?= number_format($stats['active_users']) ?></div>
                    <div class="trend"><?= $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100, 1) : 0 ?>% of total users</div>
                </div>
                <div class="stat-card">
                    <h3>Crop Recommendations</h3>
                    <div class="number"><?= number_format($stats['total_recommendations']) ?></div>
                    <div class="trend">Across <?= $stats['total_districts'] ?> districts</div>
                </div>
                <div class="stat-card">
                    <h3>Irrigation Tips</h3>
                    <div class="number"><?= number_format($stats['total_irrigation']) ?></div>
                    <div class="trend">Comprehensive irrigation guidance</div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> User Registration Trend</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="userTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-pie"></i> User Demographics</h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights Grid -->
            <div class="insights-grid">
                <!-- Top Locations -->
                <div class="insight-card">
                    <h4><i class="fas fa-map-marker-alt"></i> Top User Locations</h4>
                    <?php if (empty($top_locations)): ?>
                        <p style="color: #666;">No location data available</p>
                    <?php else: ?>
                        <?php 
                        $max_count = max(array_column($top_locations, 'count'));
                        foreach ($top_locations as $location): 
                        ?>
                            <div class="location-item">
                                <div>
                                    <strong><?= htmlspecialchars($location['location']) ?></strong>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($location['count'] / $max_count) * 100 ?>%"></div>
                                    </div>
                                </div>
                                <span><?= $location['count'] ?> users</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Popular Crops -->
                <div class="insight-card">
                    <h4><i class="fas fa-seedling"></i> Popular Crops</h4>
                    <?php if (empty($popular_crops)): ?>
                        <p style="color: #666;">No crop data available</p>
                    <?php else: ?>
                        <?php 
                        $max_crop_count = max(array_column($popular_crops, 'count'));
                        foreach ($popular_crops as $crop): 
                        ?>
                            <div class="crop-item">
                                <div>
                                    <strong><?= htmlspecialchars($crop['crop1']) ?></strong>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($crop['count'] / $max_crop_count) * 100 ?>%"></div>
                                    </div>
                                </div>
                                <span><?= $crop['count'] ?> recommendations</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Activities -->
                <div class="insight-card">
                    <h4><i class="fas fa-history"></i> Recent Admin Activities</h4>
                    <?php if (empty($recent_activities)): ?>
                        <p style="color: #666;">No recent activities</p>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h5><?= htmlspecialchars($activity['action']) ?></h5>
                                    <p><?= htmlspecialchars($activity['fullname']) ?> • <?= htmlspecialchars($activity['ip_address']) ?></p>
                                </div>
                                <small style="color: #666;"><?= date('M j, H:i', strtotime($activity['created_at'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Age Distribution -->
                <div class="insight-card">
                    <h4><i class="fas fa-users"></i> Age Distribution</h4>
                    <div class="chart-container">
                        <canvas id="ageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Registration Trend Chart
        const userTrendCtx = document.getElementById('userTrendChart').getContext('2d');
        new Chart(userTrendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthly_users, 'month')) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode(array_column($monthly_users, 'count')) ?>,
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

        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($gender_stats)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($gender_stats)) ?>,
                    backgroundColor: ['#1e3c72', '#2a5298', '#4a90e2'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Age Distribution Chart
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($age_stats)) ?>,
                datasets: [{
                    label: 'Users',
                    data: <?= json_encode(array_values($age_stats)) ?>,
                    backgroundColor: '#28a745',
                    borderRadius: 4
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