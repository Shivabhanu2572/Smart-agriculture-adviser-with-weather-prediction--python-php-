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
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                background: #f4f9ff;
                margin: 0;
                padding: 40px;
            }
            .container {
                max-width: 600px;
                background: #ffffff;
                padding: 30px;
                margin: auto;
                text-align: center;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }
            .info {
                font-size: 1.2rem;
                color: #2e7d32;
                margin-bottom: 25px;
            }
            .buttons a {
                text-decoration: none;
                padding: 10px 20px;
                background: #2e7d32;
                color: white;
                border-radius: 8px;
                margin: 0 10px;
                display: inline-block;
                transition: background 0.3s ease;
            }
            .buttons a:hover {
                background: #1b5e20;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <div class="info">
            ✅ To get smart crop advice, please complete your soil analysis first.
        </div>
        <div class="buttons">
            <a href="soil_result.php?reset=1">🔁 Do Soil Analysis</a>
            <a href="dashboard.php">🏠 Back to Dashboard</a> <!-- ✅ FIXED: Correct redirect here -->
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Step 2: Continue with Crop Advice logic if data exists
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
    (min_moisture BETWEEN ($moisture - 10) AND ($moisture + 10)) OR 
    soil_types LIKE '%$soil_type%') AND season = '$season'";

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
            font-family: 'Segoe UI', sans-serif;
            background: #f0f9f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 960px;
            background: #fff;
            padding: 30px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2e7d32;
        }
        .info, .box {
            margin: 15px 0;
            padding: 15px;
            background: #e8f5e9;
            border-left: 5px solid #43a047;
            border-radius: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #c8e6c9;
        }
        .buttons {
            margin-top: 30px;
            text-align: center;
        }
        .buttons a {
            text-decoration: none;
            padding: 10px 20px;
            background: #2e7d32;
            color: white;
            border-radius: 8px;
            margin: 0 10px;
            transition: background 0.3s ease;
        }
        .buttons a:hover {
            background: #1b5e20;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🌾 Smart Crop Advice</h1>

    <div class="info">📍 City: <strong><?= htmlspecialchars($city) ?></strong></div>
    <div class="info">pH Value: <strong><?= htmlspecialchars($ph) ?></strong></div>
    <div class="info">Moisture Level: <strong><?= htmlspecialchars($moisture) ?>%</strong></div>
    <div class="info">Soil Type: <strong><?= htmlspecialchars($soil_type) ?></strong></div>
    <div class="info">Season: <strong><?= $season ?></strong></div>
    <div class="info">Temperature: <strong><?= $temperature ?>°C</strong> | Humidity: <strong><?= $humidity ?>%</strong></div>

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
        <?php if (count($nearCrops) > 0): ?>
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
                        <td><?= $crop['min_ph'] . " - " . $crop['max_ph'] ?></td>
                        <td><?= $crop['min_moisture'] . "% - " . $crop['max_moisture'] . "%" ?></td>
                        <td><?= $crop['fertilizer'] ?? 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No near match crops found.</p>
        <?php endif; ?>
    </div>

    <div class="buttons">
        <a href="soil_result.php?reset=1">🔄 Recheck Soil Analysis</a>
        <a href="dashboard.php">🏠 Back to Dashboard</a> <!-- ✅ FIXED HERE -->
    </div>
</div>
</body>
</html>
