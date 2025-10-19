<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_irrigation'])) {
        $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
        $stage_name = mysqli_real_escape_string($conn, $_POST['stage_name']);
        $day_start = (int)$_POST['day_start'];
        $day_end = (int)$_POST['day_end'];
        $water_per_day = mysqli_real_escape_string($conn, $_POST['water_per_day']);
        $irrigation_method = mysqli_real_escape_string($conn, $_POST['irrigation_method']);
        $chem_fertilizer = mysqli_real_escape_string($conn, $_POST['chem_fertilizer']);
        $organic_fertilizer = mysqli_real_escape_string($conn, $_POST['organic_fertilizer']);
        $soil_type = mysqli_real_escape_string($conn, $_POST['soil_type']);
        $region = mysqli_real_escape_string($conn, $_POST['region']);
        $growth_duration = (int)$_POST['growth_duration'];
        $harvest_tip = mysqli_real_escape_string($conn, $_POST['harvest_tip']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        
        $sql = "INSERT INTO crop_irrigation_details (crop_name, stage_name, day_start, day_end, water_per_day, irrigation_method, chem_fertilizer, organic_fertilizer, soil_type, region, growth_duration, harvest_tip, icon) 
                VALUES ('$crop_name', '$stage_name', $day_start, $day_end, '$water_per_day', '$irrigation_method', '$chem_fertilizer', '$organic_fertilizer', '$soil_type', '$region', $growth_duration, '$harvest_tip', '$icon')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Irrigation details added successfully.";
        } else {
            $message = "Error adding irrigation details: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['update_irrigation'])) {
        $id = (int)$_POST['irrigation_id'];
        $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
        $stage_name = mysqli_real_escape_string($conn, $_POST['stage_name']);
        $day_start = (int)$_POST['day_start'];
        $day_end = (int)$_POST['day_end'];
        $water_per_day = mysqli_real_escape_string($conn, $_POST['water_per_day']);
        $irrigation_method = mysqli_real_escape_string($conn, $_POST['irrigation_method']);
        $chem_fertilizer = mysqli_real_escape_string($conn, $_POST['chem_fertilizer']);
        $organic_fertilizer = mysqli_real_escape_string($conn, $_POST['organic_fertilizer']);
        $soil_type = mysqli_real_escape_string($conn, $_POST['soil_type']);
        $region = mysqli_real_escape_string($conn, $_POST['region']);
        $growth_duration = (int)$_POST['growth_duration'];
        $harvest_tip = mysqli_real_escape_string($conn, $_POST['harvest_tip']);
        $icon = mysqli_real_escape_string($conn, $_POST['icon']);
        
        $sql = "UPDATE crop_irrigation_details SET 
                crop_name='$crop_name', stage_name='$stage_name', day_start=$day_start, day_end=$day_end, 
                water_per_day='$water_per_day', irrigation_method='$irrigation_method', chem_fertilizer='$chem_fertilizer', 
                organic_fertilizer='$organic_fertilizer', soil_type='$soil_type', region='$region', 
                growth_duration=$growth_duration, harvest_tip='$harvest_tip', icon='$icon' 
                WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Irrigation details updated successfully.";
        } else {
            $message = "Error updating irrigation details: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['delete_irrigation'])) {
        $id = (int)$_POST['irrigation_id'];
        $sql = "DELETE FROM crop_irrigation_details WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Irrigation details deleted successfully.";
        } else {
            $message = "Error deleting irrigation details: " . mysqli_error($conn);
        }
    }
}

// Get irrigation details with pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$crop_filter = isset($_GET['crop']) ? mysqli_real_escape_string($conn, $_GET['crop']) : '';

$where_clause = "WHERE 1=1";
if (!empty($search)) {
    $where_clause .= " AND (crop_name LIKE '%$search%' OR stage_name LIKE '%$search%' OR irrigation_method LIKE '%$search%')";
}
if (!empty($crop_filter)) {
    $where_clause .= " AND crop_name = '$crop_filter'";
}

// Get total count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_irrigation_details $where_clause");
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get irrigation details
$sql = "SELECT * FROM crop_irrigation_details $where_clause ORDER BY crop_name, day_start LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
$irrigation_details = [];
while ($row = mysqli_fetch_assoc($result)) {
    $irrigation_details[] = $row;
}

// Get unique crops for filter
$crops = [];
$crop_result = mysqli_query($conn, "SELECT DISTINCT crop_name FROM crop_irrigation_details ORDER BY crop_name");
while ($row = mysqli_fetch_assoc($crop_result)) {
    $crops[] = $row['crop_name'];
}

$stages = ['Seedling', 'Vegetative', 'Flowering', 'Fruiting', 'Harvest'];
$methods = ['Drip Irrigation', 'Sprinkler', 'Flood Irrigation', 'Furrow Irrigation', 'Manual Watering'];
$soil_types = ['Clay', 'Sandy', 'Loamy', 'Silt', 'Peaty', 'Chalky'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Irrigation Tips | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        .add-btn {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .add-btn:hover {
            background: #218838;
        }
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        .filters input, .filters select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .filters button {
            padding: 8px 16px;
            background: #1e3c72;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .irrigation-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .table-header h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f1f3f4;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #007bff;
            color: white;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        .pagination a, .pagination span {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        .pagination a:hover {
            background: #f8f9fa;
        }
        .pagination .current {
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
            margin: 2% auto;
            padding: 30px;
            border-radius: 12px;
            width: 95%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .stage-cell {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tip-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                <div><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
                <small><?= htmlspecialchars($_SESSION['admin_role']) ?></small>
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
            <a href="admin_irrigation.php" class="nav-item active"><i class="fas fa-tint"></i> Irrigation Tips</a>
            <a href="admin_prices.php" class="nav-item"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Irrigation Tips</h1>
                <button class="add-btn" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Add New Irrigation Details
                </button>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <div class="filters">
                <form method="GET" style="display: flex; gap: 15px; align-items: center;">
                    <input type="text" name="search" placeholder="Search crops, stages, or methods..." value="<?= htmlspecialchars($search) ?>">
                    <select name="crop">
                        <option value="">All Crops</option>
                        <?php foreach ($crops as $crop): ?>
                            <option value="<?= htmlspecialchars($crop) ?>" <?= $crop_filter === $crop ? 'selected' : '' ?>>
                                <?= htmlspecialchars($crop) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> Filter</button>
                    <a href="admin_irrigation.php" class="btn btn-cancel">Clear</a>
                </form>
            </div>

            <div class="irrigation-table">
                <div class="table-header">
                    <h3>Irrigation Details (<?= $total_records ?> total records)</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Crop</th>
                                <th>Stage</th>
                                <th>Days</th>
                                <th>Water/Day</th>
                                <th>Method</th>
                                <th>Soil Type</th>
                                <th>Region</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($irrigation_details)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; color: #666;">No irrigation details found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($irrigation_details as $detail): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($detail['crop_name']) ?></td>
                                        <td class="stage-cell" title="<?= htmlspecialchars($detail['stage_name']) ?>">
                                            <?= htmlspecialchars($detail['stage_name']) ?>
                                        </td>
                                        <td><?= $detail['day_start'] ?>-<?= $detail['day_end'] ?></td>
                                        <td><?= htmlspecialchars($detail['water_per_day']) ?></td>
                                        <td><?= htmlspecialchars($detail['irrigation_method']) ?></td>
                                        <td><?= htmlspecialchars($detail['soil_type']) ?></td>
                                        <td><?= htmlspecialchars($detail['region']) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-edit" onclick="editIrrigation(<?= $detail['id'] ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-delete" onclick="deleteIrrigation(<?= $detail['id'] ?>, '<?= htmlspecialchars($detail['crop_name']) ?> - <?= htmlspecialchars($detail['stage_name']) ?>')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&crop=<?= urlencode($crop_filter) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Irrigation Modal -->
    <div id="irrigationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Irrigation Details</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="irrigationForm" method="POST">
                <input type="hidden" name="irrigation_id" id="irrigation_id">
                <input type="hidden" name="add_irrigation" id="add_irrigation" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="crop_name">Crop Name</label>
                        <input type="text" id="crop_name" name="crop_name" required list="crops">
                        <datalist id="crops">
                            <?php foreach ($crops as $crop): ?>
                                <option value="<?= htmlspecialchars($crop) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="stage_name">Growth Stage</label>
                        <select id="stage_name" name="stage_name" required>
                            <option value="">Select Stage</option>
                            <?php foreach ($stages as $stage): ?>
                                <option value="<?= $stage ?>"><?= $stage ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="day_start">Start Day</label>
                        <input type="number" id="day_start" name="day_start" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="day_end">End Day</label>
                        <input type="number" id="day_end" name="day_end" min="1" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="water_per_day">Water Per Day</label>
                        <input type="text" id="water_per_day" name="water_per_day" placeholder="e.g., 5L, 10L" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="irrigation_method">Irrigation Method</label>
                        <select id="irrigation_method" name="irrigation_method" required>
                            <option value="">Select Method</option>
                            <?php foreach ($methods as $method): ?>
                                <option value="<?= $method ?>"><?= $method ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="chem_fertilizer">Chemical Fertilizer</label>
                        <input type="text" id="chem_fertilizer" name="chem_fertilizer" placeholder="e.g., NPK 20-20-20">
                    </div>
                    
                    <div class="form-group">
                        <label for="organic_fertilizer">Organic Fertilizer</label>
                        <input type="text" id="organic_fertilizer" name="organic_fertilizer" placeholder="e.g., Vermicompost">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="soil_type">Soil Type</label>
                        <select id="soil_type" name="soil_type" required>
                            <option value="">Select Soil Type</option>
                            <?php foreach ($soil_types as $soil): ?>
                                <option value="<?= $soil ?>"><?= $soil ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="region">Region</label>
                        <input type="text" id="region" name="region" placeholder="e.g., Karnataka, Maharashtra" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="growth_duration">Growth Duration (days)</label>
                        <input type="number" id="growth_duration" name="growth_duration" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon">Icon (emoji)</label>
                        <input type="text" id="icon" name="icon" placeholder="e.g., 🌱, 🌿, 🌸, 🍅">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="harvest_tip">Harvest Tip</label>
                    <textarea id="harvest_tip" name="harvest_tip" placeholder="Tips for harvesting this crop..."></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Irrigation Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <p>Are you sure you want to delete this irrigation detail: <strong id="delete_irrigation_name"></strong>?</p>
            <p style="color: #dc3545; font-size: 14px;">This action cannot be undone.</p>
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="irrigation_id" id="delete_irrigation_id">
                <input type="hidden" name="delete_irrigation" value="1">
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete Irrigation Details</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Irrigation Details';
            document.getElementById('irrigationForm').reset();
            document.getElementById('add_irrigation').value = '1';
            document.getElementById('irrigation_id').value = '';
            document.getElementById('irrigationModal').style.display = 'block';
        }

        function editIrrigation(irrigationId) {
            // In a real application, you would fetch irrigation data via AJAX
            // For now, we'll show the modal with basic structure
            document.getElementById('modalTitle').textContent = 'Edit Irrigation Details';
            document.getElementById('add_irrigation').value = '';
            document.getElementById('irrigation_id').value = irrigationId;
            document.getElementById('irrigationModal').style.display = 'block';
        }

        function deleteIrrigation(irrigationId, irrigationName) {
            document.getElementById('delete_irrigation_id').value = irrigationId;
            document.getElementById('delete_irrigation_name').textContent = irrigationName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('irrigationModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 