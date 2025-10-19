<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}
require_once "db.php";

// Defaults for form
$ph = isset($_POST['ph']) ? floatval($_POST['ph']) : 6.5;
$moisture = isset($_POST['moisture']) ? floatval($_POST['moisture']) : 50;
$soil_type = isset($_POST['soil_type']) ? $_POST['soil_type'] : 'Loamy';
$city = isset($_POST['city']) ? $_POST['city'] : 'Bangalore';
$show_results = $_SERVER['REQUEST_METHOD'] === 'POST';

$apiKey = "7b46741e515713880330945106c0d3d8";
$weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";
$response = @file_get_contents($weatherUrl);
$weatherData = json_decode($response, true);
$temperature = $weatherData['main']['temp'] ?? 28;
$humidity = $weatherData['main']['humidity'] ?? 65;

$month = date('n');
$season = ($month >= 6 && $month <= 10) ? 'Kharif' : (($month >= 11 || $month <= 2) ? 'Rabi' : 'Zaid');

$exactCrops = $nearCrops = [];
if ($show_results) {
    $exactQuery = "SELECT * FROM crop_master WHERE 
        min_ph <= $ph AND max_ph >= $ph 
        AND min_moisture <= $moisture AND max_moisture >= $moisture 
        AND FIND_IN_SET('$soil_type', soil_types) 
        AND season = '$season'";
    $nearQuery = "SELECT * FROM crop_master WHERE (
        (min_ph BETWEEN ($ph - 0.5) AND ($ph + 0.5)) OR 
        (min_moisture BETWEEN ($moisture - 10) AND ($moisture + 10))
    ) AND season = '$season'";
    $exactResult = $conn->query($exactQuery);
    $nearResult = $conn->query($nearQuery);
    if ($exactResult && $exactResult->num_rows > 0) {
        while ($row = $exactResult->fetch_assoc()) {
            $exactCrops[] = $row;
        }
    }
    if ($nearResult && $nearResult->num_rows > 0) {
        while ($row = $nearResult->fetch_assoc()) {
            $found = false;
            foreach ($exactCrops as $crop) {
                if ($crop['name'] === $row['name']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $nearCrops[] = $row;
            }
        }
    }
}
$soil_ph_ranges = [
    'Sandy' => '5.5 - 7.0',
    'Loamy' => '6.0 - 7.5',
    'Clay' => '6.0 - 7.0',
    'Alluvial' => '6.0 - 7.5',
    'Black' => '6.0 - 8.0',
    'Red' => '5.5 - 7.0',
    'Laterite' => '5.0 - 6.5',
    'Peaty' => '5.0 - 6.5',
    'Saline' => '7.5 - 8.5',
    'Mountain' => '4.5 - 6.5',
    'Forest' => '5.0 - 6.5',
    'Mixed Red and Black' => '6.0 - 8.0',
    'Coastal Alluvial' => '5.5 - 7.5',
    'Lateritic Gravelly' => '5.5 - 6.5',
    'Red Sandy Loam' => '5.5 - 7.0',
    'Deep Black' => '6.0 - 8.0',
    'Medium Black' => '6.0 - 7.5',
    'Shallow Black' => '6.0 - 7.5',
];
$soil_types = array_keys($soil_ph_ranges);

// --- CRUD for crop_master entries ---
$crud_message = '';
if (isset($_POST['crud_action'])) {
    if ($_POST['crud_action'] === 'add') {
        $name = $_POST['crud_name'];
        $soil_types = $_POST['crud_soil_types'];
        $season = $_POST['crud_season'];
        $min_ph = floatval($_POST['crud_min_ph']);
        $max_ph = floatval($_POST['crud_max_ph']);
        $min_moisture = floatval($_POST['crud_min_moisture']);
        $max_moisture = floatval($_POST['crud_max_moisture']);
        $stmt = $conn->prepare("INSERT INTO crop_master (name, soil_types, season, min_ph, max_ph, min_moisture, max_moisture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssdddd', $name, $soil_types, $season, $min_ph, $max_ph, $min_moisture, $max_moisture);
        $stmt->execute();
        $crud_message = 'Crop added successfully!';
    } elseif ($_POST['crud_action'] === 'edit') {
        $id = intval($_POST['crud_id']);
        $name = $_POST['crud_name'];
        $soil_types = $_POST['crud_soil_types'];
        $season = $_POST['crud_season'];
        $min_ph = floatval($_POST['crud_min_ph']);
        $max_ph = floatval($_POST['crud_max_ph']);
        $min_moisture = floatval($_POST['crud_min_moisture']);
        $max_moisture = floatval($_POST['crud_max_moisture']);
        $stmt = $conn->prepare("UPDATE crop_master SET name=?, soil_types=?, season=?, min_ph=?, max_ph=?, min_moisture=?, max_moisture=? WHERE id=?");
        $stmt->bind_param('sssddddi', $name, $soil_types, $season, $min_ph, $max_ph, $min_moisture, $max_moisture, $id);
        $stmt->execute();
        $crud_message = 'Crop updated successfully!';
    } elseif ($_POST['crud_action'] === 'delete') {
        $id = intval($_POST['crud_id']);
        $conn->query("DELETE FROM crop_master WHERE id=$id");
        $crud_message = 'Crop deleted.';
    }
}
// Fetch all crop_master entries
$crops = [];
$res = $conn->query("SELECT * FROM crop_master ORDER BY name ASC");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $crops[] = $row;
    }
}

// Check if any crop has a non-empty fertilizer value
$showFertilizerCol = false;
foreach ($crops as $crop) {
    if (!empty($crop['fertilizer'])) {
        $showFertilizerCol = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Crop Advice | Smart Agriculture</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; color: #222; }
        .admin-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 20px 30px; font-size: 1.5rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .admin-info { display: flex; align-items: center; gap: 15px; }
        .admin-avatar { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .logout-btn { background: rgba(255,255,255,0.1); color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); }
        .container { display: flex; min-height: calc(100vh - 80px); }
        .sidebar { width: 250px; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1); padding: 20px 0; }
        .nav-item { padding: 15px 25px; display: flex; align-items: center; gap: 12px; color: #666; text-decoration: none; transition: all 0.3s; border-left: 3px solid transparent; }
        .nav-item:hover, .nav-item.active { background: #f8f9fa; color: #1e3c72; border-left-color: #1e3c72; }
        .nav-item i { width: 20px; }
        .main-content { flex: 1; padding: 30px; }
        /* Existing styles for info-grid, box, table, modal, etc. */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .info { background: #e8f5e9; border-left: 5px solid #43a047; border-radius: 8px; padding: 12px 16px; font-size: 1.05rem; color: #2e7d32; font-weight: 600; }
        .box { margin: 18px 0 0 0; padding: 18px 16px 16px 16px; background: #f1f8e9; border-radius: 10px; box-shadow: 0 2px 8px rgba(67,160,71,0.07); }
        .box h3 { margin-top: 0; color: #388e3c; font-size: 1.18rem; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px 8px; border: 1px solid #c8e6c9; text-align: left; font-size: 1rem; }
        th { background-color: #c8e6c9; color: #2e7d32; font-weight: 700; }
        tr { transition: background 0.2s; }
        tr:hover { background: #e0f2f1; }
        @media (max-width: 700px) { .container { padding: 12px 2px; } .info-grid { grid-template-columns: 1fr; } th, td { font-size: 0.95rem; } }
    </style>
</head>
<body>
    <div class="admin-header">
        <span><i class="fas fa-leaf"></i> Admin Crop Advice Module</span>
        <div class="admin-info">
            <div class="admin-avatar"><i class="fas fa-user"></i></div>
            <div>
                <div><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
                <small><?= htmlspecialchars($_SESSION['admin_role']) ?></small>
            </div>
            <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    <div class="container">
        <div class="sidebar">
            <a href="admin_dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php" class="nav-item"><i class="fas fa-users"></i> Manage Users</a>
            <a href="admin_crops.php" class="nav-item"><i class="fas fa-seedling"></i> Crop Recommendations</a>
            <a href="admin_crop_advice.php" class="nav-item active"><i class="fas fa-leaf"></i> Crop Advice</a>
            <a href="admin_irrigation.php" class="nav-item"><i class="fas fa-tint"></i> Irrigation Tips</a>
            <a href="admin_prices.php" class="nav-item"><i class="fas fa-chart-line"></i> Market Prices</a>
            <a href="admin_ai_crop_monitor.php" class="nav-item"><i class="fas fa-robot"></i> AI Crop Monitor</a>
            <a href="admin_analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i> Analytics</a>
            <a href="admin_settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </div>
        <div class="main-content">
            <!-- Soil/pH/Moisture Reference Section -->
            <div class="box" style="margin-bottom:24px;">
                <h3 style="color:#1e3c72;">🔍 Check Soil, pH, and Moisture Reference</h3>
                <form style="display:flex;flex-wrap:wrap;gap:18px;align-items:center;">
                    <div style="min-width:160px;">
                        <label for="ref_soil_type" style="font-weight:600;">Soil Type</label>
                        <select id="ref_soil_type" onchange="updatePhMoistureRef()" style="padding:8px 14px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            <?php foreach ($soil_types as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="min-width:160px;">
                        <label for="ref_ph" style="font-weight:600;">pH Range</label>
                        <input type="text" id="ref_ph" readonly style="padding:8px 14px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;background:#f9f9f9;">
                    </div>
                    <div style="min-width:160px;">
                        <label for="ref_moisture" style="font-weight:600;">Moisture Range (%)</label>
                        <input type="text" id="ref_moisture" readonly style="padding:8px 14px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;background:#f9f9f9;">
                    </div>
                </form>
                <script>
                // Reference data for soil type
                const soilPhRef = {
                    'Sandy': '5.5 - 7.0', 'Loamy': '6.0 - 7.5', 'Clay': '6.0 - 7.0', 'Alluvial': '6.0 - 7.5',
                    'Black': '6.0 - 8.0', 'Red': '5.5 - 7.0', 'Laterite': '5.0 - 6.5', 'Peaty': '5.0 - 6.5',
                    'Saline': '7.5 - 8.5', 'Mountain': '4.5 - 6.5', 'Forest': '5.0 - 6.5',
                    'Mixed Red and Black': '6.0 - 8.0', 'Coastal Alluvial': '5.5 - 7.5', 'Lateritic Gravelly': '5.5 - 6.5',
                    'Red Sandy Loam': '5.5 - 7.0', 'Deep Black': '6.0 - 8.0', 'Medium Black': '6.0 - 7.5', 'Shallow Black': '6.0 - 7.5'
                };
                const soilMoistureRef = {
                    'Sandy': '20 - 50', 'Loamy': '40 - 70', 'Clay': '50 - 80', 'Alluvial': '40 - 80',
                    'Black': '40 - 70', 'Red': '20 - 60', 'Laterite': '30 - 60', 'Peaty': '60 - 80',
                    'Saline': '30 - 60', 'Mountain': '40 - 70', 'Forest': '60 - 80',
                    'Mixed Red and Black': '40 - 70', 'Coastal Alluvial': '50 - 80', 'Lateritic Gravelly': '30 - 60',
                    'Red Sandy Loam': '30 - 60', 'Deep Black': '40 - 70', 'Medium Black': '40 - 70', 'Shallow Black': '30 - 60'
                };
                function updatePhMoistureRef() {
                    var st = document.getElementById('ref_soil_type').value;
                    document.getElementById('ref_ph').value = soilPhRef[st] || '';
                    document.getElementById('ref_moisture').value = soilMoistureRef[st] || '';
                }
                window.onload = updatePhMoistureRef;
                </script>
            </div>
            <div class="box" style="margin-top:0;">
                <h3 style="color:#1e3c72;">🌱 Manage Crops (crop_master)</h3>
                <?php if ($crud_message): ?>
                    <div style="background:#d4edda;color:#155724;padding:10px 18px;border-radius:7px;margin-bottom:18px;"> <?= htmlspecialchars($crud_message) ?> </div>
                <?php endif; ?>
                <button onclick="showAddCropModal()" style="background:#1e3c72;color:#fff;padding:8px 22px;border:none;border-radius:7px;font-size:1rem;font-weight:600;margin-bottom:18px;cursor:pointer;">+ Add Crop</button>
                <div style="overflow-x:auto;">
                    <table>
                        <tr>
                            <th>Name</th><th>Soil Types</th><th>Season</th><th>pH</th><th>Moisture</th><th>Actions</th>
                        </tr>
                        <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?= htmlspecialchars($crop['name']) ?></td>
                                <td><?= htmlspecialchars($crop['soil_types']) ?></td>
                                <td><?= htmlspecialchars($crop['season']) ?></td>
                                <td><?= htmlspecialchars($crop['min_ph']) ?> - <?= htmlspecialchars($crop['max_ph']) ?></td>
                                <td><?= htmlspecialchars($crop['min_moisture']) ?>% - <?= htmlspecialchars($crop['max_moisture']) ?>%</td>
                                <td>
                                    <button onclick="editCrop(<?= $crop['id'] ?>, <?= htmlspecialchars(json_encode($crop)) ?>)" style="background:#ffc107;color:#333;padding:4px 12px;border:none;border-radius:5px;">Edit</button>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this crop?');">
                                        <input type="hidden" name="crud_action" value="delete">
                                        <input type="hidden" name="crud_id" value="<?= $crop['id'] ?>">
                                        <button type="submit" style="background:#dc3545;color:#fff;padding:4px 12px;border:none;border-radius:5px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($crops)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#888;">No crops found.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <!-- Add/Edit Modal -->
            <div id="cropModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100vw;height:100vh;background:rgba(30,60,114,0.12);align-items:center;justify-content:center;">
                <div style="background:#fff;padding:32px 28px;border-radius:14px;max-width:520px;width:95vw;box-shadow:0 4px 24px rgba(30,60,114,0.18);position:relative;">
                    <form method="post" id="cropForm">
                        <input type="hidden" name="crud_action" id="crud_action" value="add">
                        <input type="hidden" name="crud_id" id="crud_id">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                            <div>
                                <label>Crop Name</label>
                                <input type="text" name="crud_name" id="crud_name" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                            <div>
                                <label>Soil Types (comma separated)</label>
                                <input type="text" name="crud_soil_types" id="crud_soil_types" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                            <div>
                                <label>Season</label>
                                <select name="crud_season" id="crud_season" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                                    <option value="Kharif">Kharif</option>
                                    <option value="Rabi">Rabi</option>
                                    <option value="Zaid">Zaid</option>
                                </select>
                            </div>
                            <div>
                                <label>Min pH</label>
                                <input type="number" step="0.1" name="crud_min_ph" id="crud_min_ph" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                            <div>
                                <label>Max pH</label>
                                <input type="number" step="0.1" name="crud_max_ph" id="crud_max_ph" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                            <div>
                                <label>Min Moisture (%)</label>
                                <input type="number" step="0.1" name="crud_min_moisture" id="crud_min_moisture" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                            <div>
                                <label>Max Moisture (%)</label>
                                <input type="number" step="0.1" name="crud_max_moisture" id="crud_max_moisture" required style="width:100%;padding:8px 10px;border-radius:7px;border:1.5px solid #bdbdbd;font-size:1rem;">
                            </div>
                        </div>
                        <div style="margin-top:18px;text-align:right;">
                            <button type="button" onclick="closeCropModal()" style="background:#6c757d;color:#fff;padding:7px 18px;border:none;border-radius:7px;margin-right:8px;">Cancel</button>
                            <button type="submit" style="background:#1e3c72;color:#fff;padding:7px 18px;border:none;border-radius:7px;">Save</button>
                        </div>
                    </form>
                    <button onclick="closeCropModal()" style="position:absolute;top:10px;right:16px;background:none;border:none;font-size:1.5rem;color:#888;cursor:pointer;">&times;</button>
                </div>
            </div>
            <script>
            function showAddCropModal() {
                document.getElementById('cropForm').reset();
                document.getElementById('crud_action').value = 'add';
                document.getElementById('crud_id').value = '';
                document.getElementById('cropModal').style.display = 'flex';
            }
            function editCrop(id, data) {
                document.getElementById('crud_action').value = 'edit';
                document.getElementById('crud_id').value = id;
                document.getElementById('crud_name').value = data.name;
                document.getElementById('crud_soil_types').value = data.soil_types;
                document.getElementById('crud_season').value = data.season;
                document.getElementById('crud_min_ph').value = data.min_ph;
                document.getElementById('crud_max_ph').value = data.max_ph;
                document.getElementById('crud_min_moisture').value = data.min_moisture;
                document.getElementById('crud_max_moisture').value = data.max_moisture;
                document.getElementById('cropModal').style.display = 'flex';
            }
            function closeCropModal() {
                document.getElementById('cropModal').style.display = 'none';
            }
            </script>
        </div>
    </div>
</body>
</html> 