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
    if (isset($_POST['add_crop'])) {
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $month = mysqli_real_escape_string($conn, $_POST['month']);
        $crop1 = mysqli_real_escape_string($conn, $_POST['crop1']);
        $crop2 = mysqli_real_escape_string($conn, $_POST['crop2']);
        $crop3 = mysqli_real_escape_string($conn, $_POST['crop3']);
        $summary = mysqli_real_escape_string($conn, $_POST['summary']);
        
        $sql = "INSERT INTO district_crop_recommendation (district, month, crop1, crop2, crop3, summary) 
                VALUES ('$district', '$month', '$crop1', '$crop2', '$crop3', '$summary')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Crop recommendation added successfully.";
        } else {
            $message = "Error adding crop recommendation: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['update_crop'])) {
        $id = (int)$_POST['crop_id'];
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $month = mysqli_real_escape_string($conn, $_POST['month']);
        $crop1 = mysqli_real_escape_string($conn, $_POST['crop1']);
        $crop2 = mysqli_real_escape_string($conn, $_POST['crop2']);
        $crop3 = mysqli_real_escape_string($conn, $_POST['crop3']);
        $summary = mysqli_real_escape_string($conn, $_POST['summary']);
        
        $sql = "UPDATE district_crop_recommendation SET 
                district='$district', month='$month', crop1='$crop1', crop2='$crop2', crop3='$crop3', summary='$summary' 
                WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Crop recommendation updated successfully.";
        } else {
            $message = "Error updating crop recommendation: " . mysqli_error($conn);
        }
    } elseif (isset($_POST['delete_crop'])) {
        $id = (int)$_POST['crop_id'];
        $sql = "DELETE FROM district_crop_recommendation WHERE id=$id";
        
        if (mysqli_query($conn, $sql)) {
            $message = "Crop recommendation deleted successfully.";
        } else {
            $message = "Error deleting crop recommendation: " . mysqli_error($conn);
        }
    }
}

// Get crop recommendations with pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$district_filter = isset($_GET['district']) ? mysqli_real_escape_string($conn, $_GET['district']) : '';

$where_clause = "WHERE 1=1";
if (!empty($search)) {
    $where_clause .= " AND (district LIKE '%$search%' OR crop1 LIKE '%$search%' OR crop2 LIKE '%$search%' OR crop3 LIKE '%$search%')";
}
if (!empty($district_filter)) {
    $where_clause .= " AND district = '$district_filter'";
}

// Get total count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM district_crop_recommendation $where_clause");
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $limit);

// Get crop recommendations
$sql = "SELECT * FROM district_crop_recommendation $where_clause ORDER BY district, month LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
$crops = [];
while ($row = mysqli_fetch_assoc($result)) {
    $crops[] = $row;
}

// Get unique districts for filter
$districts = [];
$district_result = mysqli_query($conn, "SELECT DISTINCT district FROM district_crop_recommendation ORDER BY district");
while ($row = mysqli_fetch_assoc($district_result)) {
    $districts[] = $row['district'];
}

$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crop Recommendations | Admin Panel</title>
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
        .crops-table {
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
            margin: 5% auto;
            padding: 30px;
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
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
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
            height: 100px;
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
        .summary-cell {
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
            <a href="admin_crops.php" class="nav-item active"><i class="fas fa-seedling"></i> Crop Recommendations</a>
            <a href="admin_crop_advice.php" class="nav-item"><i class="fas fa-leaf"></i> Crop Advice</a>
            <a href="admin_irrigation.php" class="nav-item"><i class="fas fa-tint"></i> Irrigation Tips</a>
            <a href="admin_prices.php" class="nav-item"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Crop Recommendations</h1>
                <button class="add-btn" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Add New Recommendation
                </button>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <div class="filters">
                <form method="GET" style="display: flex; gap: 15px; align-items: center;">
                    <input type="text" name="search" placeholder="Search crops or districts..." value="<?= htmlspecialchars($search) ?>">
                    <select name="district">
                        <option value="">All Districts</option>
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= htmlspecialchars($district) ?>" <?= $district_filter === $district ? 'selected' : '' ?>>
                                <?= htmlspecialchars($district) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"><i class="fas fa-search"></i> Filter</button>
                    <a href="admin_crops.php" class="btn btn-cancel">Clear</a>
                </form>
            </div>

            <div class="crops-table">
                <div class="table-header">
                    <h3>Crop Recommendations (<?= $total_records ?> total records)</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>District</th>
                                <th>Month</th>
                                <th>Crop 1</th>
                                <th>Crop 2</th>
                                <th>Crop 3</th>
                                <th>Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($crops)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #666;">No crop recommendations found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($crops as $crop): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($crop['district']) ?></td>
                                        <td><?= htmlspecialchars($crop['month']) ?></td>
                                        <td><?= htmlspecialchars($crop['crop1']) ?></td>
                                        <td><?= htmlspecialchars($crop['crop2']) ?></td>
                                        <td><?= htmlspecialchars($crop['crop3']) ?></td>
                                        <td class="summary-cell" title="<?= htmlspecialchars($crop['summary']) ?>">
                                            <?= htmlspecialchars($crop['summary']) ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-edit" onclick="editCrop(<?= $crop['id'] ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-delete" onclick="deleteCrop(<?= $crop['id'] ?>, '<?= htmlspecialchars($crop['district']) ?> - <?= htmlspecialchars($crop['month']) ?>')">
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
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&district=<?= urlencode($district_filter) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Crop Modal -->
    <div id="cropModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Crop Recommendation</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="cropForm" method="POST">
                <input type="hidden" name="crop_id" id="crop_id">
                <input type="hidden" name="add_crop" id="add_crop" value="1">
                
                <div class="form-group">
                    <label for="district">District</label>
                    <input type="text" id="district" name="district" required list="districts">
                    <datalist id="districts">
                        <?php foreach ($districts as $district): ?>
                            <option value="<?= htmlspecialchars($district) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="month">Month</label>
                    <select id="month" name="month" required>
                        <option value="">Select Month</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="crop1">Primary Crop</label>
                    <input type="text" id="crop1" name="crop1" required>
                </div>
                
                <div class="form-group">
                    <label for="crop2">Secondary Crop</label>
                    <input type="text" id="crop2" name="crop2" required>
                </div>
                
                <div class="form-group">
                    <label for="crop3">Tertiary Crop</label>
                    <input type="text" id="crop3" name="crop3" required>
                </div>
                
                <div class="form-group">
                    <label for="summary">Summary/Description</label>
                    <textarea id="summary" name="summary" required></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Recommendation</button>
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
            <p>Are you sure you want to delete this crop recommendation: <strong id="delete_crop_name"></strong>?</p>
            <p style="color: #dc3545; font-size: 14px;">This action cannot be undone.</p>
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="crop_id" id="delete_crop_id">
                <input type="hidden" name="delete_crop" value="1">
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete Recommendation</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Crop Recommendation';
            document.getElementById('cropForm').reset();
            document.getElementById('add_crop').value = '1';
            document.getElementById('crop_id').value = '';
            document.getElementById('cropModal').style.display = 'block';
        }

        function editCrop(cropId) {
            // In a real application, you would fetch crop data via AJAX
            // For now, we'll show the modal with basic structure
            document.getElementById('modalTitle').textContent = 'Edit Crop Recommendation';
            document.getElementById('add_crop').value = '';
            document.getElementById('crop_id').value = cropId;
            document.getElementById('cropModal').style.display = 'block';
        }

        function deleteCrop(cropId, cropName) {
            document.getElementById('delete_crop_id').value = cropId;
            document.getElementById('delete_crop_name').textContent = cropName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('cropModal').style.display = 'none';
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