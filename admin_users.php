<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$message = "";
$action = $_GET['action'] ?? '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        $result = mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
        if ($result) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
    } elseif (isset($_POST['update_user'])) {
        $user_id = (int)$_POST['user_id'];
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $age = (int)$_POST['age'];
        $gender = mysqli_real_escape_string($conn, $_POST['gender']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        
        $sql = "UPDATE users SET fullname='$fullname', email='$email', location='$location', age=$age, gender='$gender', contact='$contact' WHERE id=$user_id";
        if (mysqli_query($conn, $sql)) {
            $message = "User updated successfully.";
        } else {
            $message = "Error updating user.";
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE fullname LIKE '%$search%' OR email LIKE '%$search%' OR location LIKE '%$search%'";
}

// Get total count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $where_clause");
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $limit);

// Get users
$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Panel</title>
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
        .search-box {
            display: flex;
            gap: 10px;
        }
        .search-box input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 300px;
        }
        .search-box button {
            padding: 10px 20px;
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
        .users-table {
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
            padding: 15px;
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
        .btn-view {
            background: #28a745;
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
            max-width: 500px;
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
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
            <a href="admin_users.php" class="nav-item active"><i class="fas fa-users"></i> Manage Users</a>
            <a href="admin_crops.php" class="nav-item"><i class="fas fa-seedling"></i> Crop Recommendations</a>
            <a href="admin_crop_advice.php" class="nav-item"><i class="fas fa-leaf"></i> Crop Advice</a>
            <a href="admin_irrigation.php" class="nav-item"><i class="fas fa-tint"></i> Irrigation Tips</a>
            <a href="admin_prices.php" class="nav-item"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">Manage Users</h1>
                <div class="search-box">
                    <form method="GET" style="display: flex; gap: 10px;">
                        <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <div class="users-table">
                <div class="table-header">
                    <h3>User List (<?= $total_users ?> total users)</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Location</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Contact</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; color: #666;">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['location']) ?></td>
                                        <td><?= htmlspecialchars($user['age']) ?></td>
                                        <td><?= htmlspecialchars($user['gender']) ?></td>
                                        <td><?= htmlspecialchars($user['contact']) ?></td>
                                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-edit" onclick="editUser(<?= $user['id'] ?>)">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-delete" onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['fullname']) ?>')">
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
                            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="update_user" value="1">
                
                <div class="form-group">
                    <label for="edit_fullname">Full Name</label>
                    <input type="text" id="edit_fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_location">Location</label>
                    <input type="text" id="edit_location" name="location" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_age">Age</label>
                    <input type="number" id="edit_age" name="age" min="1" max="120" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_gender">Gender</label>
                    <select id="edit_gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_contact">Contact</label>
                    <input type="text" id="edit_contact" name="contact" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Changes</button>
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
            <p>Are you sure you want to delete user: <strong id="delete_user_name"></strong>?</p>
            <p style="color: #dc3545; font-size: 14px;">This action cannot be undone.</p>
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="user_id" id="delete_user_id">
                <input type="hidden" name="delete_user" value="1">
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-delete">Delete User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(userId) {
            // In a real application, you would fetch user data via AJAX
            // For now, we'll use a simple approach
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('editModal').style.display = 'block';
        }

        function deleteUser(userId, userName) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_user_name').textContent = userName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
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