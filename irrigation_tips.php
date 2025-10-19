<?php
session_start();
require_once 'db_connection.php'; // Connect to database

// Get crop from session or GET
$auto_crop = $_SESSION['recommended_crop'] ?? null;
$selected_crop = $_GET['crop'] ?? $auto_crop ?? 'Tomato';

// After $selected_crop is determined
$_SESSION['last_advice'] = $selected_crop . ' irrigation';

// Fetch crop list for dropdown
$crops = [];
$result = mysqli_query($conn, "SELECT DISTINCT crop_name FROM crop_irrigation_details ORDER BY crop_name ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $crops[$row['crop_name']] = $row['crop_name'];
}

// Fetch crop details
$query = "SELECT * FROM crop_irrigation_details WHERE crop_name = '" . mysqli_real_escape_string($conn, $selected_crop) . "' ORDER BY day_start ASC";
$result = mysqli_query($conn, $query);

$stages = [];
$soil_type = '';
$region = '';
$total_days = 0;
$harvest_tip = '';
while ($row = mysqli_fetch_assoc($result)) {
    $stages[] = [
        "name" => $row['stage_name'],
        "start" => $row['day_start'],
        "end" => $row['day_end'],
        "icon" => $row['icon'],
        "water" => $row['water_per_day'],
        "method" => $row['irrigation_method'],
        "chem_fertilizer" => $row['chem_fertilizer'],
        "organic_fertilizer" => $row['organic_fertilizer']
    ];
    $soil_type = $row['soil_type'];
    $region = $row['region'];
    $total_days = max($total_days, $row['growth_duration']);
    $harvest_tip = $row['harvest_tip'];
}

$current_day = 32;
$stage = "Unknown";
$current = null;
foreach ($stages as $s) {
    if ($current_day >= $s["start"] && $current_day <= $s["end"]) {
        $stage = $s["name"];
        $current = $s;
        break;
    }
}

// Function to detect user's city based on IP
function getUserCityFromIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $locationData = @file_get_contents("http://ip-api.com/json/$ip");
    }

$apiKey = "7b46741e515713880330945106c0d3d8"; // Replace with actual OpenWeatherMap API key
$city = getUserCityFromIP();
$weatherData = @file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric");

if ($weatherData) {
    $weather = json_decode($weatherData, true);
    $temp_c = $weather['main']['temp'] ?? 30;
    $rain_mm = $weather['rain']['1h'] ?? ($weather['rain']['3h'] ?? 0);
} else {
    $temp_c = 30;
    $rain_mm = 0;
}

$moisture = 35; // Can be updated to real-time soil data
$advice = "";
$irrigation_warning = "";
if ($moisture >= 50) {
    $irrigation_warning = "🟢 Moisture is sufficient. No need to irrigate today.";
} elseif ($rain_mm > 10) {
    $irrigation_warning = "🔵 Rain expected today. Delay irrigation to avoid overwatering.";
} else {
    $advice = "Irrigate with {$current['water']}L/day using {$current['method']} method.";
    if ($rain_mm > 0 && $rain_mm < 10) {
        $advice .= " Reduce water by 30% due to light rain forecast.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Irrigation & Fertilizer Tips</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,hi,kn,ta,te,ml,bn,gu,mr,pa',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <style>
        :root {
            --primary: #2d6a4f;
            --secondary: #38b000;
            --accent: #f9f9f9;
            --bg: #f5fdf7;
            --card-bg: #fff;
            --border-radius: 18px;
            --shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
            --text: #222;
            --muted: #666;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(120deg, var(--bg) 60%, #e0ffe6 100%);
            min-height: 100vh;
            color: var(--text);
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 16px;
        }
        .translate-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 32px 28px 28px 28px;
            margin-bottom: 32px;
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        .header p {
            color: var(--muted);
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .dashboard-link {
            display: inline-block;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .dashboard-link:hover {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.12);
        }
        .section {
            margin-bottom: 28px;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .info-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px 24px;
            margin-bottom: 0;
        }
        .info-list p {
            margin: 0;
            font-size: 1rem;
            padding: 8px 0;
        }
        .info-list strong {
            color: var(--secondary);
            font-weight: 600;
        }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 8px;
            height: 12px;
            margin: 10px 0 0 0;
            width: 100%;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            height: 100%;
            border-radius: 8px;
            transition: width 0.5s;
        }
        .advice-box {
            background: #e9fbe5;
            border-left: 5px solid var(--secondary);
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 18px;
            color: #205c2c;
            font-size: 1.08rem;
        }
        .advice-box.critical {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #7c5a00;
        }
        .weather-box {
            background: #e3f0ff;
            border-left: 5px solid #2196f3;
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 18px;
            color: #1a3a5d;
            font-size: 1.08rem;
        }
        .table-section {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.04);
        }
        th, td {
            padding: 12px 10px;
            text-align: center;
        }
        th {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
        }
        tr:nth-child(even) {
            background: #f7fafc;
        }
        tr:hover {
            background: #e9fbe5;
        }
        .harvest-box {
            background: #fffbe7;
            border-left: 5px solid #ff9800;
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 18px;
            color: #7c4a00;
            font-size: 1.08rem;
        }
        @media (max-width: 700px) {
            .container {
                padding: 0 4vw;
            }
            .card {
                padding: 18px 6vw 18px 6vw;
            }
            .info-list {
                flex-direction: column;
                gap: 10px;
            }
            th, td {
                padding: 8px 4px;
                font-size: 0.98rem;
            }
        }
    </style>
</head>
<body>
<div class="translate-container" id="google_translate_element"></div>
<div class="container">
    <div class="header">
        <a href="dashboard.php" class="dashboard-link"><i class="fas fa-home"></i> Dashboard</a>
        <h1><i class="fas fa-seedling"></i> Smart Irrigation & Fertilizer Advisor</h1>
        <p>Get personalized irrigation and fertilizer recommendations for your crops</p>
    </div>
    <div class="card">
        <form method="get" style="margin-bottom: 24px;">
            <label for="crop" style="font-weight:600;color:var(--primary);margin-right:10px;">Select Your Crop:</label>
            <select id="crop" name="crop" onchange="this.form.submit()" style="padding:8px 16px;border-radius:8px;border:1.5px solid var(--secondary);font-size:1rem;">
                <?php foreach ($crops as $cropName): ?>
                    <option value="<?= $cropName ?>" <?= $cropName === $selected_crop ? 'selected' : '' ?>><?= $cropName ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><input type="submit" value="Go"></noscript>
        </form>
        <div class="section">
            <div class="section-title"><i class="fas fa-leaf"></i> Crop Information</div>
            <div class="info-list">
                <p><strong>Crop:</strong> <?= $selected_crop ?></p>
                <p><strong>Soil Type:</strong> <?= $soil_type ?></p>
                <p><strong>Region:</strong> <?= $region ?></p>
                <p><strong>Growth Duration:</strong> <?= $total_days ?> days</p>
                <p><strong>Current Stage:</strong> <?= $stage ?></p>
                <p style="flex-basis:100%;height:0;"></p>
                <p style="flex-basis:100%;margin:0;">
                    <strong>Progress:</strong> <?= $current_day ?>/<?= $total_days ?> days (<?= round(($current_day / $total_days) * 100) ?>%)
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= min(100, ($current_day / $total_days) * 100) ?>%"></div></div>
                </p>
            </div>
        </div>
        <div class="section weather-box">
            <div class="section-title"><i class="fas fa-cloud-sun"></i> Weather Conditions</div>
            <div class="info-list">
                <p><strong>Location:</strong> <?= htmlspecialchars($city) ?></p>
                <p><strong>Temperature:</strong> <?= $temp_c ?>°C</p>
                <p><strong>Rain Forecast:</strong> <?= $rain_mm ?> mm</p>
                <p><strong>Soil Moisture:</strong> <?= $moisture ?>%</p>
            </div>
        </div>
        <div class="section advice-box<?= $advice ? '' : ' critical' ?>">
            <div class="section-title"><i class="fas fa-tint"></i> Irrigation Advice</div>
            <?php if ($advice): ?>
                <strong>Action Required:</strong><br><?= $advice ?>
            <?php else: ?>
                <strong>Status:</strong><br><?= $irrigation_warning ?>
            <?php endif; ?>
        </div>
        <div class="section table-section">
            <div class="section-title"><i class="fas fa-chart-line"></i> Crop Growth Stage Summary</div>
            <table>
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Day Range</th>
                        <th>Icon</th>
                        <th>Water (L/day)</th>
                        <th>Irrigation Method</th>
                        <th>Chemical Fertilizer</th>
                        <th>Organic Fertilizer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stages as $s): ?>
                        <tr>
                            <td><strong><?= $s['name'] ?></strong></td>
                            <td><?= $s['start'] . ' - ' . $s['end'] ?></td>
                            <td><?= $s['icon'] ?? '🌱' ?></td>
                            <td><?= $s['water'] ?></td>
                            <td><?= $s['method'] ?></td>
                            <td><?= nl2br(htmlspecialchars($s['chem_fertilizer'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($s['organic_fertilizer'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($harvest_tip)): ?>
        <div class="section harvest-box">
            <div class="section-title"><i class="fas fa-scissors"></i> Harvesting Guidelines</div>
            <div><?= $harvest_tip ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
