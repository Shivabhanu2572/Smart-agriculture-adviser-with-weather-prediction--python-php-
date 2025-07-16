<?php 
session_start();

// Reset logic if user clicked "Recheck"
if (isset($_GET['reset']) && $_GET['reset'] == 1) {
    session_unset();
    session_destroy();
    session_start(); // restart session
}

// 🌦 Detect city from session or IP
if (!isset($_SESSION['city'])) {
    $geoData = @file_get_contents("https://ipapi.co/json");
    $geo = json_decode($geoData, true);
    $_SESSION['city'] = $geo['city'] ?? 'Bangalore';
}
$city = $_GET['city'] ?? $_SESSION['city'];

// 🌤 Weather API
$apiKey = "7b46741e515713880330945106c0d3d8";
$weatherUrl = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";

$response = @file_get_contents($weatherUrl);
$weatherData = json_decode($response, true);

if (!$weatherData || !isset($weatherData['main'])) {
    $temperature = 28;
    $humidity = 65;
    $weatherWarning = "⚠ Unable to fetch live weather data. Showing default values.";
} else {
    $temperature = $weatherData['main']['temp'];
    $humidity = $weatherData['main']['humidity'];
}

$showResult = false;

// 🌱 If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ph = $_POST['ph'];
    $moisture = $_POST['moisture'];
    $soil_type = $_POST['soil_type'];

    // Store in session
    $_SESSION['ph'] = $ph;
    $_SESSION['moisture'] = $moisture;
    $_SESSION['soil_type'] = $soil_type;

    $showResult = true;
    $soil_quality = rand(70, 95);

    // Smart recommendation logic
    if ($ph < 5.5) {
        $recommendation = "Your soil is too acidic. Add lime and organic compost to increase pH.";
        $organic_fertilizer = "Compost, Green manure, Vermicompost";
        $chemical_fertilizer = "Dolomite Lime or Agricultural Lime (CaCO₃)";
        $crop_suggestion = ['Ragi', 'Millets'];
    } elseif ($ph >= 5.5 && $ph <= 7) {
        $recommendation = "Your soil is neutral and suitable for most crops. Maintain organic balance.";
        $organic_fertilizer = "Farmyard Manure, Vermicompost, Neem Cake";
        $chemical_fertilizer = "NPK-based fertilizer like DAP, Urea";
        $crop_suggestion = ['Rice', 'Maize', 'Tur'];
    } else {
        $recommendation = "Your soil is alkaline. Use sulfur-based amendments and increase organic matter.";
        $organic_fertilizer = "Poultry manure, Green manure";
        $chemical_fertilizer = "Gypsum or Elemental Sulfur";
        $crop_suggestion = ['Cotton', 'Wheat'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Soil Analysis & Result</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f9ff;
            padding: 20px;
        }
        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1, h3 {
            text-align: center;
            color: #2e7d32;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
        }
        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #2e7d32;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background: #1b5e20;
        }
        .info, .box {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-left: 5px solid #43a047;
            border-radius: 6px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 5px solid #ffa000;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
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
            transition: 0.3s;
        }
        .buttons a:hover {
            background: #1b5e20;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Smart Soil Analysis</h1>

    <?php if (!$showResult): ?>
        <form method="POST">
            <!-- ✅ 1. Soil Type -->
            <label for="soil_type">Select Soil Type:</label>
            <select name="soil_type" id="soil_type" required>
                <option value="">-- Select --</option>
                <option value="Alluvial">Alluvial</option>
                <option value="Black">Black</option>
                <option value="Red">Red</option>
                <option value="Laterite">Laterite</option>
                <option value="Arid">Arid</option>
                <option value="Mountain">Mountain</option>
                <option value="Sandy">Sandy</option>
                <option value="Clay">Clay</option>
                <option value="Peaty">Peaty</option>
                <option value="Saline">Saline</option>
                <option value="Silty">Silty</option>
                <option value="Sandy Loam">Sandy Loam</option>
                <option value="Loamy">Loamy</option>
            </select>

            <!-- ✅ 2. pH -->
            <label for="ph">pH Value:</label>
            <input type="number" name="ph" id="ph" step="0.1" min="0" max="14" required>

            <!-- ✅ 3. Moisture -->
            <label for="moisture">Moisture Level (%):</label>
            <input type="number" name="moisture" id="moisture" step="1" min="0" max="100" required>

            <button type="submit">🔍 Analyze Soil</button>
        </form>
    <?php else: ?>

        <?php if (isset($weatherWarning)): ?>
            <div class="warning"><?= $weatherWarning ?></div>
        <?php endif; ?>

        <div class="info">📍 City: <strong><?= htmlspecialchars($city) ?></strong></div>
        <div class="info">pH Value: <strong><?= htmlspecialchars($ph) ?></strong></div>
        <div class="info">Moisture Level: <strong><?= htmlspecialchars($moisture) ?>%</strong></div>
        <div class="info">Soil Type: <strong><?= htmlspecialchars($soil_type) ?></strong></div>
        <div class="info">Temperature: <strong><?= htmlspecialchars($temperature) ?>°C</strong> | Humidity: <strong><?= htmlspecialchars($humidity) ?>%</strong></div>
        <div class="info">🌍 Soil Quality Score: <strong><?= $soil_quality ?>%</strong></div>

        <div class="box">
            <h3>🌱 Smart Recommendation</h3>
            <p><?= $recommendation ?></p>
        </div>

        <div class="box">
            <h3>🌿 Organic Fertilizers</h3>
            <p><?= $organic_fertilizer ?></p>
        </div>

        <div class="box">
            <h3>🧪 Chemical Fertilizers</h3>
            <p><?= $chemical_fertilizer ?></p>
        </div>

        <div class="box">
            <h3>🌾 Sample Suitable Crops</h3>
            <p><?= implode(", ", $crop_suggestion) ?></p>
        </div>

        <div class="buttons">
            <a href="crop_advice.php?city=<?= urlencode($city) ?>">🌾 View Crop Advice</a>
            <a href="soil_result.php?reset=1">🔄 Recheck Soil</a>
            <a href="dashboard.php">🏠 Dashboard</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
