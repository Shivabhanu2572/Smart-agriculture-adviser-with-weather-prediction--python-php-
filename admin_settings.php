<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];
$admin_id = $_SESSION['admin_id'];

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                
                // Verify current password
                $check_query = mysqli_query($conn, "SELECT password FROM admins WHERE id = $admin_id");
                $admin_data = mysqli_fetch_assoc($check_query);
                
                if (password_verify($current_password, $admin_data['password'])) {
                    $update_query = "UPDATE admins SET fullname = '$fullname', email = '$email'";
                    if (!empty($new_password)) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query .= ", password = '$hashed_password'";
                    }
                    $update_query .= " WHERE id = $admin_id";
                    
                    if (mysqli_query($conn, $update_query)) {
                        $_SESSION['admin_name'] = $fullname;
                        $message = "Profile updated successfully!";
                    } else {
                        $error = "Error updating profile: " . mysqli_error($conn);
                    }
                } else {
                    $error = "Current password is incorrect!";
                }
                break;
                
            case 'update_system_settings':
                $site_name = mysqli_real_escape_string($conn, $_POST['site_name']);
                $site_description = mysqli_real_escape_string($conn, $_POST['site_description']);
                $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email']);
                $max_file_size = (int)$_POST['max_file_size'];
                $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
                
                // Update or insert system settings
                $settings_query = "INSERT INTO system_settings (setting_key, setting_value) VALUES 
                    ('site_name', '$site_name'),
                    ('site_description', '$site_description'),
                    ('contact_email', '$contact_email'),
                    ('max_file_size', '$max_file_size'),
                    ('maintenance_mode', '$maintenance_mode')
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
                
                if (mysqli_query($conn, $settings_query)) {
                    $message = "System settings updated successfully!";
                } else {
                    $error = "Error updating settings: " . mysqli_error($conn);
                }
                break;
                
            case 'create_admin':
                if ($admin_role == 'super_admin') {
                    $new_fullname = mysqli_real_escape_string($conn, $_POST['new_fullname']);
                    $new_email = mysqli_real_escape_string($conn, $_POST['new_email']);
                    $new_password = $_POST['new_password'];
                    $new_role = mysqli_real_escape_string($conn, $_POST['new_role']);
                    
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $create_query = "INSERT INTO admins (fullname, email, password, role, created_at) 
                                   VALUES ('$new_fullname', '$new_email', '$hashed_password', '$new_role', NOW())";
                    
                    if (mysqli_query($conn, $create_query)) {
                        $message = "Admin account created successfully!";
                    } else {
                        $error = "Error creating admin: " . mysqli_error($conn);
                    }
                } else {
                    $error = "Only super admins can create new admin accounts!";
                }
                break;
                
            case 'delete_admin':
                if ($admin_role == 'super_admin') {
                    $delete_id = (int)$_POST['delete_id'];
                    if ($delete_id != $admin_id) {
                        if (mysqli_query($conn, "DELETE FROM admins WHERE id = $delete_id")) {
                            $message = "Admin account deleted successfully!";
                        } else {
                            $error = "Error deleting admin: " . mysqli_error($conn);
                        }
                    } else {
                        $error = "You cannot delete your own account!";
                    }
                } else {
                    $error = "Only super admins can delete admin accounts!";
                }
                break;
        }
    }
}

// Get current admin data
$admin_query = mysqli_query($conn, "SELECT * FROM admins WHERE id = $admin_id");
$admin_data = mysqli_fetch_assoc($admin_query);

// Get system settings
$settings = [];
$settings_query = mysqli_query($conn, "SELECT setting_key, setting_value FROM system_settings");
if ($settings_query) {
    while ($row = mysqli_fetch_assoc($settings_query)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Get all admin accounts
$admins_query = mysqli_query($conn, "SELECT id, fullname, email, role, created_at, last_login FROM admins ORDER BY created_at DESC");
$admins = [];
if ($admins_query) {
    while ($row = mysqli_fetch_assoc($admins_query)) {
        $admins[] = $row;
    }
} else {
    // Fallback if last_login column doesn't exist
    $admins_query = mysqli_query($conn, "SELECT id, fullname, email, role, created_at FROM admins ORDER BY created_at DESC");
    if ($admins_query) {
        while ($row = mysqli_fetch_assoc($admins_query)) {
            $row['last_login'] = null;
            $admins[] = $row;
        }
    }
}

// Get system statistics
$stats = [];
$stats['total_users'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$stats['total_recommendations'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM district_crop_recommendation"))['total'];
$stats['total_irrigation'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM crop_irrigation_details"))['total'];

// Get admin count directly from database
$admin_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM admins");
$stats['total_admins'] = mysqli_fetch_assoc($admin_count_query)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | Smart Agriculture</title>
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
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
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
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
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
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .admin-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .admin-table tr:hover {
            background: #f8f9fa;
        }
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-super {
            background: #dc3545;
            color: white;
        }
        .role-admin {
            background: #28a745;
            color: white;
        }
        .role-moderator {
            background: #ffc107;
            color: #333;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        @media (max-width: 768px) {
            .settings-grid {
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
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item active"><i class="fas fa-cog"></i> Settings</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h1 class="page-title">System Settings</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- System Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?= number_format($stats['total_users']) ?></div>
                    <div class="label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= number_format($stats['total_recommendations']) ?></div>
                    <div class="label">Crop Recommendations</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= number_format($stats['total_irrigation']) ?></div>
                    <div class="label">Irrigation Tips</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?= number_format($stats['total_admins']) ?></div>
                    <div class="label">Admin Accounts</div>
                </div>
            </div>

            <div class="settings-grid">
                <!-- Profile Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-edit"></i> Profile Settings</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" class="form-control" 
                                       value="<?= htmlspecialchars($admin_data['fullname']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($admin_data['email']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>New Password (leave blank to keep current)</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cogs"></i> System Configuration</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_system_settings">
                            
                            <div class="form-group">
                                <label>Site Name</label>
                                <input type="text" name="site_name" class="form-control" 
                                       value="<?= htmlspecialchars($settings['site_name'] ?? 'Smart Agriculture Advisor') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Site Description</label>
                                <textarea name="site_description" class="form-control" rows="3"><?= htmlspecialchars($settings['site_description'] ?? 'Your trusted partner in smart agriculture') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Contact Email</label>
                                <input type="email" name="contact_email" class="form-control" 
                                       value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Max File Upload Size (MB)</label>
                                <input type="number" name="max_file_size" class="form-control" 
                                       value="<?= htmlspecialchars($settings['max_file_size'] ?? '5') ?>" min="1" max="50">
                            </div>
                            
                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" name="maintenance_mode" id="maintenance_mode" 
                                           <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                                    <label for="maintenance_mode">Enable Maintenance Mode</label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Admin Management -->
                <?php if ($admin_role == 'super_admin'): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-plus"></i> Create New Admin</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="create_admin">
                            
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="new_fullname" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="new_email" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Role</label>
                                <select name="new_role" class="form-control" required>
                                    <option value="admin">Admin</option>
                                    <option value="moderator">Moderator</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Create Admin
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Admin Accounts List -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Admin Accounts</h3>
                </div>
                <div class="card-body">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Last Login</th>
                                <?php if ($admin_role == 'super_admin'): ?>
                                <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?= htmlspecialchars($admin['fullname']) ?></td>
                                <td><?= htmlspecialchars($admin['email']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $admin['role'] == 'super_admin' ? 'super' : ($admin['role'] == 'admin' ? 'admin' : 'moderator') ?>">
                                        <?= ucfirst($admin['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($admin['created_at'])) ?></td>
                                <td>
                                    <?= $admin['last_login'] ? date('M j, Y H:i', strtotime($admin['last_login'])) : 'Never' ?>
                                </td>
                                <?php if ($admin_role == 'super_admin' && $admin['id'] != $admin_id): ?>
                                <td>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                        <input type="hidden" name="action" value="delete_admin">
                                        <input type="hidden" name="delete_id" value="<?= $admin['id'] ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                                <?php elseif ($admin_role == 'super_admin'): ?>
                                <td>
                                    <span style="color: #666; font-size: 12px;">Current User</span>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 