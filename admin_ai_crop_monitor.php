<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_crop':
                $crop_id = (int)$_POST['crop_id'];
                // Delete associated disease reports first
                mysqli_query($conn, "DELETE FROM crop_disease_reports WHERE crop_id = $crop_id");
                // Delete the crop
                mysqli_query($conn, "DELETE FROM ai_crop_monitoring WHERE id = $crop_id");
                $message = "Crop and all associated analyses deleted successfully!";
                break;
                
            case 'delete_analysis':
                $analysis_id = (int)$_POST['analysis_id'];
                // Get image path before deleting
                $get_image = mysqli_query($conn, "SELECT image_path FROM crop_disease_reports WHERE id = $analysis_id");
                if ($get_image && $row = mysqli_fetch_assoc($get_image)) {
                    $image_path = $row['image_path'];
                    // Delete image file if exists
                    if ($image_path && file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                // Delete from database
                mysqli_query($conn, "DELETE FROM crop_disease_reports WHERE id = $analysis_id");
                $message = "Crop analysis deleted successfully!";
                break;
        }
    }
}

// Get statistics
$stats = [];

// Total crops being monitored
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM ai_crop_monitoring");
$stats['total_crops'] = mysqli_fetch_assoc($result)['total'];

// Total disease reports
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_disease_reports");
$stats['total_reports'] = mysqli_fetch_assoc($result)['total'];

// Users with AI monitoring
$result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM ai_crop_monitoring");
$stats['active_users'] = mysqli_fetch_assoc($result)['total'];

// Recent disease reports
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_disease_reports WHERE DATE(created_at) = CURDATE()");
$stats['today_reports'] = mysqli_fetch_assoc($result)['total'];

// Get all crops with user information
$crops_query = mysqli_query($conn, "
    SELECT 
        acm.*,
        u.fullname as user_name,
        u.email as user_email,
        COUNT(cdr.id) as analysis_count
    FROM ai_crop_monitoring acm
    LEFT JOIN users u ON acm.user_id = u.id
    LEFT JOIN crop_disease_reports cdr ON acm.id = cdr.crop_id
    GROUP BY acm.id
    ORDER BY acm.created_at DESC
");

// Get recent disease reports
$recent_reports_query = mysqli_query($conn, "
    SELECT 
        cdr.*,
        acm.crop_name,
        u.fullname as user_name
    FROM crop_disease_reports cdr
    JOIN ai_crop_monitoring acm ON cdr.crop_id = acm.id
    JOIN users u ON cdr.user_id = u.id
    ORDER BY cdr.created_at DESC
    LIMIT 10
");

// Get disease statistics
$disease_stats_query = mysqli_query($conn, "
    SELECT 
        detected_disease,
        COUNT(*) as count,
        AVG(confidence_score) as avg_confidence
    FROM crop_disease_reports
    GROUP BY detected_disease
    ORDER BY count DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Crop Monitor - Admin | Smart Agriculture</title>
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            font-size: 28px;
            font-weight: 700;
            color: #1e3c72;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            max-height: 400px;
            overflow-y: auto;
        }
        .crop-item, .report-item {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .crop-item:last-child, .report-item:last-child {
            margin-bottom: 0;
        }
        .crop-header, .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .crop-name {
            font-weight: 600;
            color: #1e3c72;
            font-size: 16px;
        }
        .user-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }
        .crop-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 13px;
            color: #555;
        }
        .severity-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .severity-low { background: #d4edda; color: #155724; }
        .severity-medium { background: #fff3cd; color: #856404; }
        .severity-high { background: #f8d7da; color: #721c24; }
        .severity-critical { background: #f5c6cb; color: #721c24; }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            margin-left: 8px;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
        }
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        .disease-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .disease-stat {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .disease-stat h4 {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        .disease-stat .count {
            font-size: 24px;
            font-weight: 700;
            color: #1e3c72;
        }
        .disease-stat .confidence {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        <h1><i class="fas fa-robot"></i> AI Crop Monitor - Admin</h1>
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
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item active"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <?php if (isset($message)): ?>
                <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Crops Monitored</h3>
                    <div class="number"><?= number_format($stats['total_crops']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Disease Reports</h3>
                    <div class="number"><?= number_format($stats['total_reports']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Users</h3>
                    <div class="number"><?= number_format($stats['active_users']) ?></div>
                </div>
                <div class="stat-card">
                    <h3>Today's Reports</h3>
                    <div class="number"><?= number_format($stats['today_reports']) ?></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-seedling"></i> All Monitored Crops</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($crops_query) == 0): ?>
                            <p style="color: #666; text-align: center;">No crops being monitored</p>
                        <?php else: ?>
                            <?php while ($crop = mysqli_fetch_assoc($crops_query)): ?>
                                <div class="crop-item">
                                    <div class="crop-header">
                                        <div class="crop-name"><?= htmlspecialchars($crop['crop_name']) ?></div>
                                        <div>
                                            <span class="severity-badge severity-<?= strtolower($crop['health_status']) ?>">
                                                <?= htmlspecialchars($crop['health_status']) ?>
                                            </span>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this crop and all its analyses?');">
                                                <input type="hidden" name="action" value="delete_crop">
                                                <input type="hidden" name="crop_id" value="<?= $crop['id'] ?>">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="user-info">
                                        <strong>User:</strong> <?= htmlspecialchars($crop['user_name']) ?> (<?= htmlspecialchars($crop['user_email']) ?>)
                                    </div>
                                    <div class="crop-details">
                                        <div><strong>Variety:</strong> <?= htmlspecialchars($crop['variety'] ?: 'Not specified') ?></div>
                                        <div><strong>Sowed:</strong> <?= date('M d, Y', strtotime($crop['sow_date'])) ?></div>
                                        <div><strong>Harvest:</strong> <?= date('M d, Y', strtotime($crop['expected_harvest_date'])) ?></div>
                                        <div><strong>Analyses:</strong> <?= $crop['analysis_count'] ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bug"></i> Recent Disease Reports</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($recent_reports_query) == 0): ?>
                            <p style="color: #666; text-align: center;">No disease reports found</p>
                        <?php else: ?>
                            <?php while ($report = mysqli_fetch_assoc($recent_reports_query)): ?>
                                <div class="report-item">
                                    <div class="report-header">
                                        <div class="crop-name"><?= htmlspecialchars($report['crop_name']) ?></div>
                                        <div>
                                            <span class="severity-badge severity-<?= strtolower($report['severity']) ?>">
                                                <?= htmlspecialchars($report['severity']) ?>
                                            </span>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this analysis?');">
                                                <input type="hidden" name="action" value="delete_analysis">
                                                <input type="hidden" name="analysis_id" value="<?= $report['id'] ?>">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="user-info">
                                        <strong>User:</strong> <?= htmlspecialchars($report['user_name']) ?>
                                    </div>
                                    <div class="crop-details">
                                        <div><strong>Disease:</strong> <?= htmlspecialchars($report['detected_disease']) ?></div>
                                        <div><strong>Confidence:</strong> <?= $report['confidence_score'] ?>%</div>
                                        <div><strong>Date:</strong> <?= date('M d, H:i', strtotime($report['created_at'])) ?></div>
                                        <div><strong>Status:</strong> <?= htmlspecialchars($report['status']) ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Disease Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="disease-stats">
                        <?php while ($disease = mysqli_fetch_assoc($disease_stats_query)): ?>
                            <div class="disease-stat">
                                <h4><?= htmlspecialchars($disease['detected_disease']) ?></h4>
                                <div class="count"><?= $disease['count'] ?></div>
                                <div class="confidence">Avg: <?= round($disease['avg_confidence'], 1) ?>%</div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="chart-container">
                        <canvas id="diseaseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Disease statistics chart
        const ctx = document.getElementById('diseaseChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Healthy Plants', 'Early Blight', 'Powdery Mildew', 'Root Rot', 'Bacterial Spot'],
                datasets: [{
                    data: [65, 15, 10, 5, 5],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#17a2b8',
                        '#dc3545',
                        '#6f42c1'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
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
    </script>
</body>
</html> 