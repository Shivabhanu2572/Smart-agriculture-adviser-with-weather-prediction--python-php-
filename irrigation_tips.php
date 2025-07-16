<?php
session_start();
require_once 'db_connection.php'; // Connect to database

// Get crop from session or GET
$auto_crop = $_SESSION['recommended_crop'] ?? null;
$selected_crop = $_GET['crop'] ?? $auto_crop ?? 'Tomato';

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
    <title>Irrigation & Fertilizer Tips</title>
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
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5fdf7;
            margin: 0;
            padding: 40px;
        }
        .card {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        h2 { color: #2d6a4f; }
        .section {
            background: #f0fdf4;
            padding: 15px 20px;
            border-left: 5px solid #38b000;
            margin: 20px 0;
            border-radius: 10px;
        }
        ul { margin: 0; padding-left: 20px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #d0e0d0;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #d4f5dc;
        }
        .btn-dashboard {
            background: #2d6a4f;
            color: white;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            float: right;
        }
    </style>
</head>
<body>
<div id="google_translate_element" style="float:right;"></div>
<div class="card">
    <a href="dashboard.php" class="btn-dashboard">🏠 Back to Dashboard</a>
    <h2>🌾 Smart Irrigation & Fertilizer Advisor</h2>
    <form method="get">
        <label for="crop">Select Crop:</label>
        <select id="crop" name="crop" onchange="this.form.submit()">
            <?php foreach ($crops as $cropName): ?>
                <option value="<?= $cropName ?>" <?= $cropName === $selected_crop ? 'selected' : '' ?>><?= $cropName ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><input type="submit" value="Go"></noscript>
    </form>

    <div class="section">
        <strong>Crop:</strong> <?= $selected_crop ?> <br>
        <strong>Soil Type:</strong> <?= $soil_type ?> <br>
        <strong>Region:</strong> <?= $region ?> <br>
        <strong>Growth Duration:</strong> <?= $total_days ?> days <br>
        <strong>Current Day:</strong> <?= $current_day ?>/<?= $total_days ?> (<?= $stage ?> Stage)
    </div>

    <div class="section">
        <strong>🌤️ Weather (<?= htmlspecialchars($city) ?>):</strong><br>
        Rain Forecast: <?= $rain_mm ?> mm<br>
        Temperature: <?= $temp_c ?>°C<br>
        Current Moisture: <?= $moisture ?>%
    </div>

    <div class="section">
        <strong>💧 Irrigation Advice:</strong><br>
        <?= $advice ?: $irrigation_warning ?>
    </div>

    <div class="section">
        <strong>📊 Crop Growth Stage Summary:</strong>
        <table>
            <tr>
                <th>Stage</th>
                <th>Day Range</th>
                <th>Icon</th>
                <th>Water (L/day)</th>
                <th>Irrigation Method</th>
                <th>Chemical Fertilizer</th>
                <th>Organic Fertilizer</th>
            </tr>
            <?php foreach ($stages as $s): ?>
                <tr>
                    <td><?= $s['name'] ?></td>
                    <td><?= $s['start'] . " - " . $s['end'] ?></td>
                    <td><?= $s['icon'] ?? '' ?></td>
                    <td><?= $s['water'] ?></td>
                    <td><?= $s['method'] ?></td>
                    <td><?= nl2br(htmlspecialchars($s['chem_fertilizer'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($s['organic_fertilizer'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php if (!empty($harvest_tip)): ?>
    <div class="section">
        <strong>🌾 Harvesting Guidelines:</strong>
        <p><?= $harvest_tip ?></p>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
