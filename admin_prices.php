<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_price':
                $commodity = mysqli_real_escape_string($conn, $_POST['commodity']);
                $market = mysqli_real_escape_string($conn, $_POST['market']);
                $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
                $variety = mysqli_real_escape_string($conn, $_POST['variety'] ?? '');
                $modal_price = (float)$_POST['modal_price'];
                $min_price = (float)$_POST['min_price'];
                $max_price = (float)$_POST['max_price'];
                $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
                $state = mysqli_real_escape_string($conn, $_POST['state']);
                
                $insert_query = "INSERT INTO market_prices (commodity, market, district, variety, modal_price, min_price, max_price, arrival_date, state, created_at) 
                               VALUES ('$commodity', '$market', '$district', '$variety', $modal_price, $min_price, $max_price, '$arrival_date', '$state', NOW())";
                
                if (mysqli_query($conn, $insert_query)) {
                    $message = "Price entry added successfully!";
                } else {
                    $error = "Error adding price: " . mysqli_error($conn);
                }
                break;
                
            case 'update_price':
                $price_id = (int)$_POST['price_id'];
                $commodity = mysqli_real_escape_string($conn, $_POST['commodity']);
                $market = mysqli_real_escape_string($conn, $_POST['market']);
                $district = mysqli_real_escape_string($conn, $_POST['district'] ?? '');
                $variety = mysqli_real_escape_string($conn, $_POST['variety'] ?? '');
                $modal_price = (float)$_POST['modal_price'];
                $min_price = (float)$_POST['min_price'];
                $max_price = (float)$_POST['max_price'];
                $arrival_date = mysqli_real_escape_string($conn, $_POST['arrival_date']);
                $state = mysqli_real_escape_string($conn, $_POST['state']);
                
                $update_query = "UPDATE market_prices SET 
                               commodity = '$commodity', 
                               market = '$market', 
                               district = '$district',
                               variety = '$variety',
                               modal_price = $modal_price, 
                               min_price = $min_price, 
                               max_price = $max_price, 
                               arrival_date = '$arrival_date', 
                               state = '$state' 
                               WHERE id = $price_id";
                
                if (mysqli_query($conn, $update_query)) {
                    $message = "Price entry updated successfully!";
                } else {
                    $error = "Error updating price: " . mysqli_error($conn);
                }
                break;
                
            case 'delete_price':
                $price_id = (int)$_POST['price_id'];
                if (mysqli_query($conn, "DELETE FROM market_prices WHERE id = $price_id")) {
                    $message = "Price entry deleted successfully!";
                } else {
                    $error = "Error deleting price: " . mysqli_error($conn);
                }
                break;
                
            case 'sync_api':
                // Sync with external API
                $apiKey = "579b464db66ec23bdd0000011ec418109033452d47392bb62daee529";
                $url = "https://api.data.gov.in/resource/9ef84268-d588-465a-a308-a864a43d0070?api-key=$apiKey&format=json&limit=10000";
                $response = @file_get_contents($url);
                $data = json_decode($response, true);
                
                if ($data && !empty($data['records'])) {
                    $synced_count = 0;
                    
                    // Get all unique dates and sync all data
                    $dates = array_unique(array_column($data['records'], 'arrival_date'));
                    rsort($dates); // Sort dates in descending order
                    
                    foreach ($data['records'] as $record) {
                        $commodity = mysqli_real_escape_string($conn, $record['commodity']);
                        $market = mysqli_real_escape_string($conn, $record['market']);
                        $district = mysqli_real_escape_string($conn, $record['district'] ?? '');
                        $variety = mysqli_real_escape_string($conn, $record['variety'] ?? '');
                        $modal_price = (float)$record['modal_price'];
                        $min_price = (float)$record['min_price'];
                        $max_price = (float)$record['max_price'];
                        $arrival_date = mysqli_real_escape_string($conn, $record['arrival_date']);
                        $state = mysqli_real_escape_string($conn, $record['state']);
                        
                        $sync_query = "INSERT INTO market_prices (commodity, market, district, variety, modal_price, min_price, max_price, arrival_date, state, created_at) 
                                     VALUES ('$commodity', '$market', '$district', '$variety', $modal_price, $min_price, $max_price, '$arrival_date', '$state', NOW())
                                     ON DUPLICATE KEY UPDATE 
                                     modal_price = $modal_price, 
                                     min_price = $min_price, 
                                     max_price = $max_price,
                                     district = '$district',
                                     variety = '$variety'";
                        
                        if (mysqli_query($conn, $sync_query)) {
                            $synced_count++;
                        }
                    }
                    $message = "Successfully synced $synced_count price entries from API! Data from " . count($dates) . " different dates.";
                } else {
                    $error = "Failed to fetch data from API or no data available. Response: " . substr($response, 0, 200);
                }
                break;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['show_all']) ? 10000 : 50; // Show more entries or all if requested
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$commodity_filter = isset($_GET['commodity']) ? mysqli_real_escape_string($conn, $_GET['commodity']) : '';
$market_filter = isset($_GET['market']) ? mysqli_real_escape_string($conn, $_GET['market']) : '';
$state_filter = isset($_GET['state']) ? mysqli_real_escape_string($conn, $_GET['state']) : '';

// Build query
$where_conditions = [];
if ($search) {
    $where_conditions[] = "(commodity LIKE '%$search%' OR market LIKE '%$search%' OR state LIKE '%$search%')";
}
if ($commodity_filter) {
    $where_conditions[] = "commodity = '$commodity_filter'";
}
if ($market_filter) {
    $where_conditions[] = "market = '$market_filter'";
}
if ($state_filter) {
    $where_conditions[] = "state = '$state_filter'";
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get data directly from API like user module
$apiKey = "579b464db66ec23bdd0000011ec418109033452d47392bb62daee529";
    $url = "https://api.data.gov.in/resource/9ef84268-d588-465a-a308-a864a43d0070?api-key=$apiKey&format=json&limit=10000";
$response = @file_get_contents($url);
$data = json_decode($response, true);

if ($data && !empty($data['records'])) {
    // Get all unique dates and find recent dates (last 7 days)
    $dates = array_unique(array_column($data['records'], 'arrival_date'));
    rsort($dates);
    $latestDate = $dates[0];
    
    // Get recent dates (last 7 days) to include more states
    $recentDates = array_slice($dates, 0, 7);
    
    // Filter data based on search and filters
    $search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
    $commodity_filter = isset($_GET['commodity']) ? $_GET['commodity'] : '';
    $market_filter = isset($_GET['market']) ? $_GET['market'] : '';
    $state_filter = isset($_GET['state']) ? $_GET['state'] : '';
    $date_filter = isset($_GET['date']) ? $_GET['date'] : '';
    
    $filtered = array_filter($data['records'], function($item) use ($search, $recentDates, $commodity_filter, $market_filter, $state_filter, $date_filter) {
        // Show data from recent dates (last 7 days) to include more states
        if (!in_array($item['arrival_date'], $recentDates)) return false;
        
        // Apply filters
        if ($commodity_filter && $item['commodity'] !== $commodity_filter) return false;
        if ($market_filter && $item['market'] !== $market_filter) return false;
        if ($state_filter && $item['state'] !== $state_filter) return false;
        if ($date_filter && $item['arrival_date'] !== $date_filter) return false;
        
        // Apply search
        if ($search !== '') {
            $fields = ['commodity', 'variety', 'market', 'district', 'state'];
            $found = false;
            foreach ($fields as $field) {
                if (isset($item[$field]) && strpos(strtolower($item[$field]), $search) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) return false;
        }
        
        return true;
    });
    
    // Sort by modal price (highest first)
    usort($filtered, function($a, $b) {
        return (int)$b['modal_price'] <=> (int)$a['modal_price'];
    });
    
    $total_prices = count($filtered);
    $prices = array_slice($filtered, $offset, $limit);
    
    // Get filter options from API data
    $commodities = [];
    $markets = [];
    $states = [];
    
    foreach ($data['records'] as $item) {
        if (!in_array($item['commodity'], $commodities)) $commodities[] = $item['commodity'];
        if (!in_array($item['market'], $markets)) $markets[] = $item['market'];
        if (!in_array($item['state'], $states)) $states[] = $item['state'];
    }
    sort($commodities);
    sort($markets);
    sort($states);
    
    // Calculate statistics
    $prices_array = array_column($filtered, 'modal_price');
    $stats = [
        'total_entries' => $total_prices,
        'avg_price' => count($prices_array) > 0 ? array_sum($prices_array) / count($prices_array) : 0,
        'highest_price' => count($prices_array) > 0 ? max($prices_array) : 0,
        'lowest_price' => count($prices_array) > 0 ? min($prices_array) : 0
    ];
    
    $total_pages = ceil($total_prices / $limit);
} else {
    $total_prices = 0;
    $total_pages = 0;
    $prices = [];
    $commodities = [];
    $markets = [];
    $states = [];
    $stats = [
        'total_entries' => 0,
        'avg_price' => 0,
        'highest_price' => 0,
        'lowest_price' => 0
    ];
    $latestDate = date('Y-m-d');
}



    // Generate chart data from API data
    $trends = [];
    $commodity_comparison = [];
    $state_analysis = [];
    $market_analysis = [];
    
    if ($data && !empty($data['records'])) {
        // Get last 30 days of data for trends
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        $trend_data = array_filter($data['records'], function($item) use ($thirty_days_ago) {
            return $item['arrival_date'] >= $thirty_days_ago;
        });
        
        // Group by commodity and date for trends
        $trend_groups = [];
        foreach ($trend_data as $item) {
            $key = $item['commodity'] . '_' . $item['arrival_date'];
            if (!isset($trend_groups[$key])) {
                $trend_groups[$key] = [
                    'commodity' => $item['commodity'],
                    'date' => $item['arrival_date'],
                    'prices' => []
                ];
            }
            $trend_groups[$key]['prices'][] = (float)$item['modal_price'];
        }
        
        foreach ($trend_groups as $group) {
            $trends[] = [
                'commodity' => $group['commodity'],
                'avg_price' => array_sum($group['prices']) / count($group['prices']),
                'date' => $group['date']
            ];
        }
        
        // Commodity comparison (from recent dates)
        $recent_data = array_filter($data['records'], function($item) use ($recentDates) {
            return in_array($item['arrival_date'], $recentDates);
        });
        
        $commodity_groups = [];
        foreach ($recent_data as $item) {
            if (!isset($commodity_groups[$item['commodity']])) {
                $commodity_groups[$item['commodity']] = [];
            }
            $commodity_groups[$item['commodity']][] = (float)$item['modal_price'];
        }
        
        foreach ($commodity_groups as $commodity => $price_array) {
            $commodity_comparison[] = [
                'commodity' => $commodity,
                'avg_price' => array_sum($price_array) / count($price_array),
                'count' => count($price_array)
            ];
        }
        
        // Sort by average price
        usort($commodity_comparison, function($a, $b) {
            return $b['avg_price'] <=> $a['avg_price'];
        });
        $commodity_comparison = array_slice($commodity_comparison, 0, 15);
        
        // State analysis
        $state_groups = [];
        foreach ($recent_data as $item) {
            if (!isset($state_groups[$item['state']])) {
                $state_groups[$item['state']] = [];
            }
            $state_groups[$item['state']][] = (float)$item['modal_price'];
        }
        
        foreach ($state_groups as $state => $price_array) {
            $state_analysis[] = [
                'state' => $state,
                'avg_price' => array_sum($price_array) / count($price_array),
                'count' => count($price_array)
            ];
        }
        
        usort($state_analysis, function($a, $b) {
            return $b['avg_price'] <=> $a['avg_price'];
        });
        $state_analysis = array_slice($state_analysis, 0, 10);
        
        // Market analysis
        $market_groups = [];
        foreach ($recent_data as $item) {
            if (!isset($market_groups[$item['market']])) {
                $market_groups[$item['market']] = [];
            }
            $market_groups[$item['market']][] = (float)$item['modal_price'];
        }
        
        foreach ($market_groups as $market => $price_array) {
            $market_analysis[] = [
                'market' => $market,
                'avg_price' => array_sum($price_array) / count($price_array),
                'count' => count($price_array)
            ];
        }
        
        usort($market_analysis, function($a, $b) {
            return $b['avg_price'] <=> $a['avg_price'];
        });
        $market_analysis = array_slice($market_analysis, 0, 10);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Prices Management | Admin Panel</title>
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
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
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
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #1e3c72;
            color: white;
        }
        .btn-primary:hover {
            background: #152a5e;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            max-width: 1200px;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #1e3c72;
            margin-bottom: 8px;
        }
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        .filters-section {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            max-width: 1200px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            align-items: end;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }
        .prices-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        .table-container {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 100%;
        }
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .prices-table table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .prices-table th,
        .prices-table td {
            padding: 8px 4px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .prices-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .prices-table tr:hover {
            background: #f8f9fa;
        }
        .price-value {
            font-weight: 600;
            color: #28a745;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }
        .pagination .active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .chart-container {
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow-x: auto;
            max-width: 1200px;
        }
        .chart-container h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .chart-wrapper {
            min-width: 400px;
            max-width: 100%;
            height: 300px;
        }
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
            }
            .action-buttons {
                flex-direction: column;
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
            <a href="admin_prices.php" class="nav-item active"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Market Prices Management</h1>
                <div>
                    <a href="?<?= http_build_query(array_merge($_GET, ['show_all' => '1'])) ?>" class="btn btn-info" style="margin-right: 10px;">
                        <i class="fas fa-list"></i> Show All
                    </a>
                    <a href="check_api_states.php" class="btn btn-secondary" style="margin-right: 10px;" target="_blank">
                        <i class="fas fa-search"></i> Check API Data
                    </a>
                    <button class="btn btn-warning" onclick="openModal('syncModal')">
                        <i class="fas fa-sync"></i> Sync API
                    </button>
                    <button class="btn btn-success" onclick="openModal('addModal')">
                        <i class="fas fa-plus"></i> Add Price
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- Data Update Info -->
            <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <strong>📊 Data Source Information:</strong>
                <ul style="margin: 8px 0 0 20px; padding: 0;">
                    <li>Data comes from Government of India's data.gov.in API</li>
                    <li>API is updated by government officials, not automatically</li>
                    <li>Different states may have data from different dates</li>
                    <li>Missing states can be added manually using "Add Price" button</li>
                    <li>Use "Check API Data" to see what states are currently available</li>
                </ul>
            </div>

            <?php if ($total_prices == 0 && mysqli_num_rows($check_table) == 0): ?>
                <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404;">
                    <strong>📊 Setup Required:</strong> The market prices table doesn't exist yet. 
                    Please run <a href="create_market_prices_table.php" style="color: #1e3c72; font-weight: bold;">create_market_prices_table.php</a> 
                    to set up the database and add sample data.
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?= number_format($stats['total_entries']) ?></div>
                    <div class="label">Total Price Entries</div>
                </div>
                <div class="stat-card">
                    <div class="number">₹<?= number_format($stats['avg_price'], 2) ?></div>
                    <div class="label">Average Price</div>
                </div>
                <div class="stat-card">
                    <div class="number">₹<?= number_format($stats['highest_price'], 2) ?></div>
                    <div class="label">Highest Price</div>
                </div>
                <div class="stat-card">
                    <div class="number">₹<?= number_format($stats['lowest_price'], 2) ?></div>
                    <div class="label">Lowest Price</div>
                </div>
            </div>

            <!-- Price Trends Chart -->
            <div class="chart-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3><i class="fas fa-chart-line"></i> Price Trends Analysis</h3>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <select id="chartType" onchange="updateChart()" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="trends">Price Trends (30 Days)</option>
                            <option value="commodity">Commodity Comparison</option>
                            <option value="state">State-wise Prices</option>
                            <option value="market">Market Analysis</option>
                        </select>
                        <select id="commodityFilter" onchange="updateChart()" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="">All Commodities</option>
                            <?php foreach ($commodities as $commodity): ?>
                                <option value="<?= htmlspecialchars($commodity) ?>"><?= htmlspecialchars($commodity) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <canvas id="priceTrendsChart" height="250"></canvas>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-grid">
                    <div class="form-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" 
                               value="<?= htmlspecialchars($search) ?>" placeholder="Search commodity, market, or state">
                    </div>
                    <div class="form-group">
                        <label>Commodity</label>
                        <select name="commodity" class="form-control">
                            <option value="">All Commodities</option>
                            <?php foreach ($commodities as $commodity): ?>
                                <option value="<?= htmlspecialchars($commodity) ?>" 
                                        <?= $commodity_filter == $commodity ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($commodity) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Market</label>
                        <select name="market" class="form-control">
                            <option value="">All Markets</option>
                            <?php foreach ($markets as $market): ?>
                                <option value="<?= htmlspecialchars($market) ?>" 
                                        <?= $market_filter == $market ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($market) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <select name="state" class="form-control">
                            <option value="">All States</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= htmlspecialchars($state) ?>" 
                                        <?= $state_filter == $state ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <select name="date" class="form-control">
                            <option value="">All Recent Dates</option>
                            <?php foreach ($recentDates as $date): ?>
                                <option value="<?= htmlspecialchars($date) ?>" 
                                        <?= (isset($_GET['date']) && $_GET['date'] === $date) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($date) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Prices Table -->
            <div class="prices-table">
                <div class="table-header">
                    <h3>Price Entries (<?= number_format($total_prices) ?> total) - Recent Dates (Last 7 Days)</h3>
                    <p style="color: #666; font-size: 14px; margin-top: 5px;">
                        Showing data from recent dates to include all states. Latest date: <?= $latestDate ?>
                    </p>
                    <?php if (empty($prices) && $data && !empty($data['records'])): ?>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">
                            API returned <?= count($data['records']) ?> records, but no data matches current filters.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>District</th>
                                <th>Market</th>
                                <th>Commodity</th>
                                <th>Variety</th>
                                <th>Arrival Date</th>
                                <th>Min Price</th>
                                <th>Max Price</th>
                                <th>Modal Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($prices)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 40px; color: #666;">
                                        <?php if (!$data || empty($data['records'])): ?>
                                            <p>No crop price data found from API. Please check your connection or try again later.</p>
                                        <?php else: ?>
                                            <p>No price entries match your current filters.</p>
                                            <p>Total API records: <?= count($data['records']) ?> | Latest date: <?= $latestDate ?></p>
                                        <?php endif; ?>
                                        <div style="margin-top: 15px;">
                                            <button class="btn btn-warning btn-sm" onclick="openModal('syncModal')">Sync from API</button>
                                            <button class="btn btn-success btn-sm" onclick="openModal('addModal')" style="margin-left: 10px;">Add manually</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prices as $price): ?>
                                <?php if (is_array($price) && isset($price['state'])): ?>
                                <tr>
                                    <td><?= htmlspecialchars($price['state']) ?></td>
                                    <td><?= htmlspecialchars($price['district'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($price['market']) ?></td>
                                    <td><strong><?= htmlspecialchars($price['commodity']) ?></strong></td>
                                    <td><?= htmlspecialchars($price['variety'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($price['arrival_date']) ?></td>
                                    <td>₹<?= number_format($price['min_price'], 2) ?></td>
                                    <td>₹<?= number_format($price['max_price'], 2) ?></td>
                                    <td class="price-value">₹<?= number_format($price['modal_price'], 2) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-primary btn-sm" onclick="editPrice(<?= htmlspecialchars(json_encode($price)) ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this price entry?')">
                                                <input type="hidden" name="action" value="delete_price">
                                                <input type="hidden" name="price_id" value="<?= $price['id'] ?? 0 ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&commodity=<?= urlencode($commodity_filter) ?>&market=<?= urlencode($market_filter) ?>&state=<?= urlencode($state_filter) ?>">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&commodity=<?= urlencode($commodity_filter) ?>&market=<?= urlencode($market_filter) ?>&state=<?= urlencode($state_filter) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&commodity=<?= urlencode($commodity_filter) ?>&market=<?= urlencode($market_filter) ?>&state=<?= urlencode($state_filter) ?>">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Price Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Price Entry</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_price">
                
                <div class="form-group">
                    <label>Commodity</label>
                    <input type="text" name="commodity" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Market</label>
                    <input type="text" name="market" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>District</label>
                    <input type="text" name="district" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Variety</label>
                    <input type="text" name="variety" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Modal Price (₹)</label>
                    <input type="number" name="modal_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Minimum Price (₹)</label>
                    <input type="number" name="min_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Maximum Price (₹)</label>
                    <input type="number" name="max_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Arrival Date</label>
                    <input type="date" name="arrival_date" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Add Price Entry
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Price Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Price Entry</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_price">
                <input type="hidden" name="price_id" id="edit_price_id">
                
                <div class="form-group">
                    <label>Commodity</label>
                    <input type="text" name="commodity" id="edit_commodity" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Market</label>
                    <input type="text" name="market" id="edit_market" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>District</label>
                    <input type="text" name="district" id="edit_district" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Variety</label>
                    <input type="text" name="variety" id="edit_variety" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Modal Price (₹)</label>
                    <input type="number" name="modal_price" id="edit_modal_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Minimum Price (₹)</label>
                    <input type="number" name="min_price" id="edit_min_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>Maximum Price (₹)</label>
                    <input type="number" name="max_price" id="edit_max_price" class="form-control" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" id="edit_state" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Arrival Date</label>
                    <input type="date" name="arrival_date" id="edit_arrival_date" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Price Entry
                </button>
            </form>
        </div>
    </div>

    <!-- Sync API Modal -->
    <div id="syncModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Sync from Government API</h3>
                <span class="close" onclick="closeModal('syncModal')">&times;</span>
            </div>
            <p>This will fetch the latest market prices from the Government of India's agricultural data API and update your database.</p>
            <form method="POST">
                <input type="hidden" name="action" value="sync_api">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-sync"></i> Sync Now
                </button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
        
        // Edit price function
        function editPrice(price) {
            document.getElementById('edit_price_id').value = price.id;
            document.getElementById('edit_commodity').value = price.commodity;
            document.getElementById('edit_market').value = price.market;
            document.getElementById('edit_district').value = price.district || '';
            document.getElementById('edit_variety').value = price.variety || '';
            document.getElementById('edit_modal_price').value = price.modal_price;
            document.getElementById('edit_min_price').value = price.min_price;
            document.getElementById('edit_max_price').value = price.max_price;
            document.getElementById('edit_state').value = price.state;
            document.getElementById('edit_arrival_date').value = price.arrival_date;
            openModal('editModal');
        }
        
        // Chart data
        const trendsData = <?= json_encode($trends) ?>;
        const commodityData = <?= json_encode($commodity_comparison) ?>;
        const stateData = <?= json_encode($state_analysis) ?>;
        const marketData = <?= json_encode($market_analysis) ?>;
        
        let currentChart = null;
        const ctx = document.getElementById('priceTrendsChart').getContext('2d');
        
        function updateChart() {
            const chartType = document.getElementById('chartType').value;
            const commodityFilter = document.getElementById('commodityFilter').value;
            
            if (currentChart) {
                currentChart.destroy();
            }
            
            let chartData = {};
            let chartOptions = {};
            
            switch(chartType) {
                case 'trends':
                    // Price trends over time
                    const filteredTrends = commodityFilter ? 
                        trendsData.filter(item => item.commodity === commodityFilter) : 
                        trendsData;
                    
                    const commodityGroups = {};
                    filteredTrends.forEach(item => {
                        if (!commodityGroups[item.commodity]) {
                            commodityGroups[item.commodity] = [];
                        }
                        commodityGroups[item.commodity].push({
                            date: item.date,
                            price: parseFloat(item.avg_price)
                        });
                    });
                    
                    const datasets = [];
                    const colors = ['#1e3c72', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'];
                    let colorIndex = 0;
                    
                    Object.keys(commodityGroups).forEach(commodity => {
                        const data = commodityGroups[commodity];
                        datasets.push({
                            label: commodity,
                            data: data.map(item => item.price),
                            borderColor: colors[colorIndex % colors.length],
                            backgroundColor: colors[colorIndex % colors.length] + '20',
                            tension: 0.4,
                            fill: false
                        });
                        colorIndex++;
                    });
                    
                    chartData = {
                        type: 'line',
                        data: {
                            labels: [...new Set(filteredTrends.map(item => item.date))].sort(),
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' },
                                title: { display: true, text: 'Price Trends (Last 30 Days)' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Price (₹)' }
                                },
                                x: {
                                    title: { display: true, text: 'Date' }
                                }
                            }
                        }
                    };
                    break;
                    
                case 'commodity':
                    // Commodity comparison
                    chartData = {
                        type: 'bar',
                        data: {
                            labels: commodityData.map(item => item.commodity),
                            datasets: [{
                                label: 'Average Price (₹)',
                                data: commodityData.map(item => parseFloat(item.avg_price)),
                                backgroundColor: '#1e3c72',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                title: { display: true, text: 'Commodity Price Comparison' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Average Price (₹)' }
                                },
                                x: {
                                    title: { display: true, text: 'Commodity' }
                                }
                            }
                        }
                    };
                    break;
                    
                case 'state':
                    // State-wise analysis
                    chartData = {
                        type: 'doughnut',
                        data: {
                            labels: stateData.map(item => item.state),
                            datasets: [{
                                data: stateData.map(item => parseFloat(item.avg_price)),
                                backgroundColor: ['#1e3c72', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#6c757d', '#fd7e14', '#e83e8c']
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'right' },
                                title: { display: true, text: 'State-wise Average Prices' }
                            }
                        }
                    };
                    break;
                    
                case 'market':
                    // Market analysis
                    chartData = {
                        type: 'bar',
                        data: {
                            labels: marketData.map(item => item.market),
                            datasets: [{
                                label: 'Average Price (₹)',
                                data: marketData.map(item => parseFloat(item.avg_price)),
                                backgroundColor: '#28a745',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                title: { display: true, text: 'Market Price Analysis' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: { display: true, text: 'Average Price (₹)' }
                                },
                                x: {
                                    title: { display: true, text: 'Market' }
                                }
                            }
                        }
                    };
                    break;
            }
            
            currentChart = new Chart(ctx, chartData);
        }
        
        // Initialize chart
        updateChart();
    </script>
</body>
</html> 