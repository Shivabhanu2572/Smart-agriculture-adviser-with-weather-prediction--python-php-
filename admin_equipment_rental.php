<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include("db_connection.php");

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

$conn = mysqli_connect("localhost", "root", "", "smart_agri");

// Handle Add/Edit/Delete
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$delete_id = isset($_GET['delete']) ? intval($_GET['delete']) : 0;
$message = '';

// Delete equipment
if ($delete_id && $conn) {
    mysqli_query($conn, "DELETE FROM equipment WHERE id = $delete_id");
    $message = 'Equipment deleted successfully!';
}

// Add or update equipment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $dealer_link = mysqli_real_escape_string($conn, $_POST['dealer_link']);
    $available = isset($_POST['available']) ? 1 : 0;
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    if (isset($_POST['edit_id']) && $_POST['edit_id']) {
        $eid = intval($_POST['edit_id']);
        mysqli_query($conn, "UPDATE equipment SET name='$name', description='$description', price=$price, dealer_link='$dealer_link', available=$available, image_url='$image_url' WHERE id=$eid");
        $message = 'Equipment updated successfully!';
    } else {
        mysqli_query($conn, "INSERT INTO equipment (name, description, price, dealer_link, available, image_url) VALUES ('$name', '$description', $price, '$dealer_link', $available, '$image_url')");
        $message = 'Equipment added successfully!';
    }
}

// Fetch equipment list
$equipment = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM equipment ORDER BY id DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $equipment[] = $row;
        }
    }
}
// Fetch equipment for editing
$edit_item = null;
if ($edit_id && $conn) {
    $res = mysqli_query($conn, "SELECT * FROM equipment WHERE id = $edit_id LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $edit_item = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Agri Equipment</title>
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
        .message { background: #e8f5e9; color: #33691e; padding: 10px 16px; border-radius: 7px; margin-bottom: 18px; font-weight: 600; }
        form { margin-bottom: 30px; }
        label { display: block; margin-bottom: 6px; color: #14532d; font-weight: 600; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #dcedc8; font-size: 1rem; }
        input[type="checkbox"] { width: auto; margin-right: 8px; }
        button { background: #1e3c72; color: #fff; padding: 10px 22px; border: none; border-radius: 7px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #14532d; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #f1f8e9; color: #1e3c72; font-weight: 700; }
        tr:last-child td { border-bottom: none; }
        .actions a { margin-right: 10px; color: #1e3c72; text-decoration: none; font-weight: 600; }
        .actions a.delete { color: #b91c1c; }
        .img-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; }
        @media (max-width: 768px) {
            .container { flex-direction: column; }
            .sidebar { display: none; }
            .main-content { padding: 10px; }
        }
        @media (max-width: 700px) { th, td { font-size: 0.97rem; } }
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
            <a href="admin_dashboard.php" class="nav-item">
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
            <a href="admin_equipment_rental.php" class="nav-item active">
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
            <h2>Admin - Agri Equipment</h2>
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="edit_id" value="<?= $edit_item ? $edit_item['id'] : '' ?>">
                <label>Equipment Name</label>
                <input type="text" name="name" required value="<?= $edit_item ? htmlspecialchars($edit_item['name']) : '' ?>">
                <label>Description</label>
                <textarea name="description" required><?= $edit_item ? htmlspecialchars($edit_item['description']) : '' ?></textarea>
                <label>Rental Price (per day, ₹)</label>
                <input type="number" name="price" min="0" step="0.01" required value="<?= $edit_item ? htmlspecialchars($edit_item['price']) : '' ?>">
                <label>Dealer Link (URL or phone)</label>
                <input type="text" name="dealer_link" required value="<?= $edit_item ? htmlspecialchars($edit_item['dealer_link']) : '' ?>">
                <label>Image URL (relative or absolute)</label>
                <input type="text" name="image_url" required value="<?= $edit_item ? htmlspecialchars($edit_item['image_url']) : '' ?>">
                <label><input type="checkbox" name="available" <?= $edit_item && !$edit_item['available'] ? '' : 'checked' ?>> Available</label>
                <button type="submit"><?= $edit_item ? 'Update' : 'Add' ?> Equipment</button>
                <?php if ($edit_item): ?>
                    <a href="admin_equipment_rental.php" style="margin-left:18px;color:#b91c1c;font-weight:600;">Cancel Edit</a>
                <?php endif; ?>
            </form>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (₹/day)</th>
                    <th>Available</th>
                    <th>Dealer Link</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($equipment as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><img class="img-thumb" src="<?= htmlspecialchars($item['image_url']) ?>" alt=""></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td><?= number_format($item['price']) ?></td>
                        <td><?= $item['available'] ? 'Yes' : 'No' ?></td>
                        <td><a href="<?= htmlspecialchars($item['dealer_link']) ?>" target="_blank">Link</a></td>
                        <td class="actions">
                            <a href="admin_equipment_rental.php?edit=<?= $item['id'] ?>">Edit</a>
                            <a href="admin_equipment_rental.php?delete=<?= $item['id'] ?>" class="delete" onclick="return confirm('Delete this equipment?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html> 