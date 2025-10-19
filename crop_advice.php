<?php
session_start();
require_once "db.php";

// Step 1: If no soil session data, show guidance UI instead of redirect
if (!isset($_SESSION['ph'], $_SESSION['moisture'], $_SESSION['soil_type'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Crop Advice | Smart Agri</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Montserrat', 'Segoe UI', sans-serif;
                background: linear-gradient(120deg, #e0f7fa 0%, #f0f9f4 100%);
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                max-width: 420px;
                background: #ffffffcc;
                padding: 36px 28px 32px 28px;
                margin: auto;
                text-align: center;
                border-radius: 18px;
                box-shadow: 0 8px 32px rgba(67,160,71,0.13);
                animation: fadeIn 0.7s cubic-bezier(.4,0,.2,1);
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: none; }
            }
            .info {
                font-size: 1.18rem;
                color: #2e7d32;
                margin-bottom: 28px;
                font-weight: 600;
            }
            .buttons {
                display: flex;
                justify-content: center;
                gap: 18px;
            }
            .btn {
                text-decoration: none;
                padding: 12px 26px;
                background: linear-gradient(90deg, #43a047 70%, #66bb6a 100%);
                color: white;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1rem;
                box-shadow: 0 2px 8px rgba(67,160,71,0.10);
                transition: background 0.3s, transform 0.2s;
                border: none;
                outline: none;
                display: inline-block;
                cursor: pointer;
            }
            .btn:hover {
                background: linear-gradient(90deg, #388e3c 70%, #43a047 100%);
                transform: translateY(-2px) scale(1.04);
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="info">
            ✅ To get smart crop advice, please complete your soil analysis first.
        </div>
        <div class="buttons">
            <a class="btn" href="soil.php">🔁 Do Soil Analysis</a>
            <a class="btn" href="dashboard.php">🏠 Back to Dashboard</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Step 2: Continue with Crop Advice logic if data exists
if (isset($_POST['manual_soil_type'])) {
    $_SESSION['soil_type'] = $_POST['manual_soil_type'];
    // Optionally, you could also reset crops if needed
    header("Location: crop_advice.php" . (isset($_GET['city']) ? ('?city=' . urlencode($_GET['city'])) : ''));
    exit();
}
$ph = $_SESSION['ph'];
$moisture = $_SESSION['moisture'];
$soil_type = $_SESSION['soil_type'];

$city = $_GET['city'] ?? 'Bangalore';
$apiKey = "7b46741e515713880330945106c0d3d8";
$weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";

$response = @file_get_contents($weatherUrl);
$weatherData = json_decode($response, true);
$temperature = $weatherData['main']['temp'] ?? 28;
$humidity = $weatherData['main']['humidity'] ?? 65;

$month = date('n');
$season = ($month >= 6 && $month <= 10) ? 'Kharif' : (($month >= 11 || $month <= 2) ? 'Rabi' : 'Zaid');

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

$exactCrops = [];
$nearCrops = [];

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crop Advice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            <?php if (!isset($_GET['city'])): ?>
            try {
                const res = await fetch('https://ipapi.co/json');
                const data = await res.json();
                if (data && data.city) {
                    window.location.replace("crop_advice.php?city=" + encodeURIComponent(data.city));
                }
            } catch (e) {
                console.warn("Location detection failed.");
            }
            <?php endif; ?>
        });
    </script>
    <style>
        body {
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #e0f7fa 0%, #f0f9f4 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .container {
            max-width: 1100px;
            background: #fff;
            padding: 38px 32px 32px 32px;
            margin: 36px auto;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(67,160,71,0.13);
            animation: fadeIn 0.7s cubic-bezier(.4,0,.2,1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: none; }
        }
        h1 {
            text-align: center;
            color: #2e7d32;
            font-size: 2.2rem;
            margin-bottom: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }
        .info {
            background: #e8f5e9;
            border-left: 5px solid #43a047;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 1.08rem;
            color: #2e7d32;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(67,160,71,0.06);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            word-break: break-word;
        }
        .box {
            margin: 18px 0 0 0;
            padding: 18px 16px 16px 16px;
            background: #f1f8e9;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(67,160,71,0.07);
            animation: fadeIn 0.7s cubic-bezier(.4,0,.2,1);
        }
        .box h3 {
            margin-top: 0;
            color: #388e3c;
            font-size: 1.18rem;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px 8px;
            border: 1px solid #c8e6c9;
            text-align: left;
            font-size: 1rem;
        }
        th {
            background-color: #c8e6c9;
            color: #2e7d32;
            font-weight: 700;
        }
        tr {
            transition: background 0.2s;
        }
        tr:hover {
            background: #e0f2f1;
        }
        .buttons {
            margin-top: 32px;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 18px;
        }
        .btn {
            text-decoration: none;
            padding: 12px 26px;
            background: linear-gradient(90deg, #43a047 70%, #66bb6a 100%);
            color: white;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(67,160,71,0.10);
            transition: background 0.3s, transform 0.2s;
            border: none;
            outline: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn:hover {
            background: linear-gradient(90deg, #388e3c 70%, #43a047 100%);
            transform: translateY(-2px) scale(1.04);
        }
        /* Loading spinner */
        .spinner {
            display: none;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(255,255,255,0.7);
            z-index: 9999;
        }
        .spinner .loader {
            border: 6px solid #e0e0e0;
            border-top: 6px solid #43a047;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 900px) {
            .info-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }
        @media (max-width: 700px) {
            .container { padding: 16px 4px; }
            .info-grid { grid-template-columns: 1fr; }
            th, td { font-size: 0.95rem; }
            .info { font-size: 1rem; }
        }
    </style>
    <script>
    // Show spinner on navigation
    function showSpinner() {
        document.getElementById('spinner').style.display = 'flex';
    }
    </script>
</head>
<body>
<div id="spinner" class="spinner"><div class="loader"></div></div>
<div class="container">
    <div class="buttons" style="margin-bottom: 18px;">
        <a class="btn" href="soil.php" onclick="showSpinner()">🔄 Recheck Soil Analysis</a>
        <a class="btn" href="dashboard.php" onclick="showSpinner()">🏠 Back to Dashboard</a>
    </div>
    <h1>🌾 Smart Crop Advice</h1>
    <!-- Manual Soil Type Dropdown -->
    <form method="post" style="margin-bottom: 18px; text-align:center;">
        <label for="manual_soil_type" style="font-weight:600; color:#388e3c; margin-right:8px;">Check with different soil type:</label>
        <select name="manual_soil_type" id="manual_soil_type" style="padding:7px 14px; border-radius:7px; border:1.5px solid #bdbdbd; font-size:1rem;">
            <option value="Sandy"   <?= $soil_type=="Sandy"   ? 'selected' : '' ?>>Sandy</option>
            <option value="Loamy"   <?= $soil_type=="Loamy"   ? 'selected' : '' ?>>Loamy</option>
            <option value="Clay"    <?= $soil_type=="Clay"    ? 'selected' : '' ?>>Clay</option>
            <option value="Alluvial"<?= $soil_type=="Alluvial"? 'selected' : '' ?>>Alluvial</option>
            <option value="Black"   <?= $soil_type=="Black"   ? 'selected' : '' ?>>Black</option>
            <option value="Red"     <?= $soil_type=="Red"     ? 'selected' : '' ?>>Red</option>
            <option value="Laterite"<?= $soil_type=="Laterite"? 'selected' : '' ?>>Laterite</option>
            <option value="Peaty"   <?= $soil_type=="Peaty"   ? 'selected' : '' ?>>Peaty</option>
            <option value="Saline"  <?= $soil_type=="Saline"  ? 'selected' : '' ?>>Saline</option>
            <option value="Mountain"<?= $soil_type=="Mountain"? 'selected' : '' ?>>Mountain</option>
            <option value="Forest"  <?= $soil_type=="Forest"  ? 'selected' : '' ?>>Forest</option>
            <option value="Mixed Red and Black" <?= $soil_type=="Mixed Red and Black" ? 'selected' : '' ?>>Mixed Red and Black</option>
            <option value="Coastal Alluvial" <?= $soil_type=="Coastal Alluvial" ? 'selected' : '' ?>>Coastal Alluvial</option>
            <option value="Lateritic Gravelly" <?= $soil_type=="Lateritic Gravelly" ? 'selected' : '' ?>>Lateritic Gravelly</option>
            <option value="Red Sandy Loam" <?= $soil_type=="Red Sandy Loam" ? 'selected' : '' ?>>Red Sandy Loam</option>
            <option value="Deep Black" <?= $soil_type=="Deep Black" ? 'selected' : '' ?>>Deep Black</option>
            <option value="Medium Black" <?= $soil_type=="Medium Black" ? 'selected' : '' ?>>Medium Black</option>
            <option value="Shallow Black" <?= $soil_type=="Shallow Black" ? 'selected' : '' ?>>Shallow Black</option>
        </select>
        <button type="submit" class="btn" style="margin-left:10px; padding:8px 18px; font-size:1rem;">Check</button>
    </form>
    <div class="info-grid">
    <div class="info">📍 City: <strong><?= htmlspecialchars($city) ?></strong></div>
    <div class="info">pH Value: <strong><?= htmlspecialchars($ph) ?></strong></div>
    <div class="info">Moisture Level: <strong><?= htmlspecialchars($moisture) ?>%</strong></div>
    <div class="info">Soil Type: <strong><?= htmlspecialchars($soil_type) ?></strong></div>
    <div class="info">Season: <strong><?= $season ?></strong></div>
        <div class="info">Temperature: <strong><?= $temperature ?>°C</strong></div>
        <div class="info">Humidity: <strong><?= $humidity ?>%</strong></div>
    </div>
    <?php
    // Simple mapping of soil type to ideal pH range
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
    $ph_suggestion = isset($soil_ph_ranges[$soil_type]) ? $soil_ph_ranges[$soil_type] : null;
    if ($ph_suggestion): ?>
        <div style="margin: 12px 0 18px 0; padding: 12px 18px; background: #fffde7; border-left: 5px solid #fbc02d; border-radius: 8px; color: #795548; font-size: 1.08rem; font-weight: 500;">
            <strong>pH Suggestion:</strong> For <b><?= htmlspecialchars($soil_type) ?></b> soil, the ideal pH range is <b><?= $ph_suggestion ?></b>.
        </div>
    <?php endif; ?>
    <div class="box">
        <h3>✅ Exact Match Crops:</h3>
        <?php if (count($exactCrops) > 0): ?>
            <table>
                <tr>
                    <th>Crop Name</th>
                    <th>Soil Types</th>
                    <th>Season</th>
                    <th>Ideal pH</th>
                    <th>Moisture</th>
                    <th>Fertilizer</th>
                </tr>
                <?php foreach ($exactCrops as $crop): ?>
                    <tr>
                        <td><?= $crop['name'] ?></td>
                        <td><?= $crop['soil_types'] ?></td>
                        <td><?= $crop['season'] ?></td>
                        <td><?= $crop['min_ph'] . " - " . $crop['max_ph'] ?></td>
                        <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                        <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No exact match crops found.</p>
        <?php endif; ?>
    </div>
    <div class="box">
        <h3>🌿 Near Match Crops:</h3>
        <?php
        // Get crops from other soil types that match the user's pH
        $ph = floatval($ph);
        $otherSoilQuery = "SELECT name, soil_types, season, min_ph, max_ph, min_moisture, max_moisture, fertilizer FROM crop_master WHERE min_ph <= $ph AND max_ph >= $ph AND NOT FIND_IN_SET('$soil_type', soil_types) LIMIT 10";
        $otherSoilResult = $conn->query($otherSoilQuery);
        $otherSoilCrops = [];
        if ($otherSoilResult && $otherSoilResult->num_rows > 0) {
            while ($row = $otherSoilResult->fetch_assoc()) {
                $otherSoilCrops[] = $row;
            }
        }
        // Fallback: if both nearCrops and otherSoilCrops are empty, show general pH matches
        $fallbackCrops = [];
        if (count($nearCrops) === 0 && count($otherSoilCrops) === 0) {
            $fallbackQuery = "SELECT name, soil_types, season, min_ph, max_ph, min_moisture, max_moisture, fertilizer FROM crop_master WHERE min_ph <= $ph AND max_ph >= $ph LIMIT 10";
            $fallbackResult = $conn->query($fallbackQuery);
            if ($fallbackResult && $fallbackResult->num_rows > 0) {
                while ($row = $fallbackResult->fetch_assoc()) {
                    $fallbackCrops[] = $row;
                }
            }
        }
        // Always show at least 5 crops in the table
        $totalRows = count($nearCrops) + count($otherSoilCrops) + count($fallbackCrops);
        $extraCrops = [];
        if ($totalRows < 5) {
            $needed = 5 - $totalRows;
            $phEsc = floatval($ph);
            $extraQuery = "SELECT name, soil_types, season, min_ph, max_ph, min_moisture, max_moisture, fertilizer, ABS((min_ph+max_ph)/2 - $phEsc) as ph_diff FROM crop_master ORDER BY ph_diff ASC LIMIT $needed";
            $extraResult = $conn->query($extraQuery);
            if ($extraResult && $extraResult->num_rows > 0) {
                while ($row = $extraResult->fetch_assoc()) {
                    $extraCrops[] = $row;
                }
            }
        }
        ?>
        <table>
            <tr>
                <th>Crop Name</th>
                <th>Soil Types</th>
                <th>Season</th>
                <th>Ideal pH</th>
                <th>Moisture</th>
                <th>Fertilizer</th>
            </tr>
            <?php foreach ($nearCrops as $crop): ?>
                <tr>
                    <td><?= $crop['name'] ?></td>
                    <td><?= $crop['soil_types'] ?></td>
                    <td><?= $crop['season'] ?></td>
                    <td><?= htmlspecialchars($ph) ?></td>
                    <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                    <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($otherSoilCrops as $crop): ?>
                <tr style="background:#e3f2fd; color:#0d47a1;">
                    <td><?= $crop['name'] ?> <span title="Other soil type for your pH">*</span></td>
                    <td><?= $crop['soil_types'] ?></td>
                    <td><?= $crop['season'] ?></td>
                    <td><?= $crop['min_ph'] . " - " . $crop['max_ph'] ?></td>
                    <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                    <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($fallbackCrops as $crop): ?>
                <tr style="background:#f5f5f5; color:#333;">
                    <td><?= $crop['name'] ?> <span title="General pH match">**</span></td>
                    <td><?= $crop['soil_types'] ?></td>
                    <td><?= $crop['season'] ?></td>
                    <td><?= $crop['min_ph'] . " - " . $crop['max_ph'] ?></td>
                    <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                    <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($extraCrops as $crop): ?>
                <tr style="background:#fffde7; color:#795548;">
                    <td><?= $crop['name'] ?> <span title="Closest pH crop">***</span></td>
                    <td><?= $crop['soil_types'] ?></td>
                    <td><?= $crop['season'] ?></td>
                    <td><?= $crop['min_ph'] . " - " . $crop['max_ph'] ?></td>
                    <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                    <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($totalRows + count($extraCrops) === 0): ?>
            <p>No near match crops found.</p>
        <?php endif; ?>
        <?php if (count($otherSoilCrops) > 0): ?>
            <div style="margin-top:8px; font-size:0.98rem; color:#1976d2;">* Crops marked with an asterisk are from other soil types but suitable for your pH.</div>
        <?php endif; ?>
        <?php if (count($fallbackCrops) > 0): ?>
            <div style="margin-top:8px; font-size:0.98rem; color:#757575;">** Crops marked with double asterisks are general pH matches (not filtered by soil type).</div>
        <?php endif; ?>
        <?php if (count($extraCrops) > 0): ?>
            <div style="margin-top:8px; font-size:0.98rem; color:#bfa100;">*** Crops marked with triple asterisks are the closest pH matches available.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
