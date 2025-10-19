<?php 
session_start();

// Reset logic if user clicked "Recheck"
// No session_destroy or session_unset here to avoid logging out the user

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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', 'Segoe UI', sans-serif;
            background: linear-gradient(120deg, #e0f7fa 0%, #f0f9f4 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .hero {
            background: linear-gradient(90deg, #43a047 60%, #a5d6a7 100%);
            color: white;
            padding: 44px 0 24px 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(67, 160, 71, 0.08);
        }
        .hero .icon {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        .hero h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .hero p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            font-weight: 400;
            color: #e8f5e9;
        }
        .main-section {
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 24px rgba(67, 160, 71, 0.10);
            padding: 36px 32px 32px 32px;
            margin-top: -40px;
            z-index: 1;
            position: relative;
        }
        .columns {
            display: flex;
            gap: 36px;
            flex-wrap: wrap;
        }
        .col {
            flex: 1 1 320px;
            min-width: 280px;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f9fbe7;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(67, 160, 71, 0.06);
        }
        .result-table th, .result-table td {
            padding: 14px 12px;
            text-align: left;
        }
        .result-table th {
            background: #43a047;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
        }
        .result-table tr:nth-child(even) {
            background: #e8f5e9;
        }
        .section-box {
            background: #e8f5e9;
            border-left: 5px solid #43a047;
            border-radius: 10px;
            margin-bottom: 22px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 2px 8px rgba(67, 160, 71, 0.04);
        }
        .section-box h3 {
            margin-top: 0;
            color: #2e7d32;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 5px solid #ffa000;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 1.05rem;
        }
        .buttons {
            margin-top: 30px;
            text-align: center;
        }
        .buttons a {
            text-decoration: none;
            padding: 12px 28px;
            background: linear-gradient(90deg, #388e3c 60%, #43a047 100%);
            color: white;
            border-radius: 8px;
            margin: 0 10px;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(67, 160, 71, 0.10);
            transition: background 0.2s, box-shadow 0.2s;
            display: inline-block;
        }
        .buttons a:hover {
            background: linear-gradient(90deg, #1976d2 60%, #388e3c 100%);
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.18);
        }
        .footer {
            margin-top: 40px;
            background: #33691e;
            color: #e0f2f1;
            text-align: center;
            padding: 18px 0 10px 0;
            font-size: 1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 -2px 10px rgba(51, 105, 30, 0.08);
        }
        @media (max-width: 900px) {
            .main-section {
                padding: 18px 4vw 18px 4vw;
            }
            .columns {
                flex-direction: column;
                gap: 18px;
            }
        }
        @media (max-width: 600px) {
            .hero h1 {
                font-size: 1.5rem;
            }
            .main-section {
                padding: 10px 2vw 10px 2vw;
            }
        }
    </style>
</head>
<body>
<div class="hero">
    <span class="icon"><i class="fa-solid fa-seedling"></i></span>
    <h1>Smart Soil Analysis Result</h1>
    <p>Get instant insights and recommendations for your soil and crops.</p>
</div>
<div class="main-section">
    <?php if (!$showResult): ?>
        <form method="POST">
            <div class="columns">
                <div class="col">
                    <label for="soil_type"><i class="fa-solid fa-mountain-sun"></i> Select Soil Type:</label>
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
                </div>
                <div class="col">
                    <label for="ph"><i class="fa-solid fa-flask-vial"></i> pH Value:</label>
                    <input type="number" name="ph" id="ph" step="0.1" min="0" max="14" required>
                </div>
                <div class="col">
                    <label for="moisture"><i class="fa-solid fa-tint"></i> Moisture Level (%):</label>
                    <input type="number" name="moisture" id="moisture" step="1" min="0" max="100" required>
                </div>
            </div>
            <div id="recommendations"></div>
            <div class="buttons">
                <button type="submit"><i class="fa-solid fa-microscope"></i> Analyze Soil</button>
            </div>
        </form>
    <?php else: ?>
        <?php if (isset($weatherWarning)): ?>
            <div class="warning"><i class="fa-solid fa-triangle-exclamation"></i> <?= $weatherWarning ?></div>
        <?php endif; ?>
        <div class="columns">
            <div class="col">
                <table class="result-table">
                    <tr><th colspan="2"><i class="fa-solid fa-table"></i> Soil & Weather Data</th></tr>
                    <tr><td><i class="fa-solid fa-location-dot"></i> City</td><td><?= htmlspecialchars($city) ?></td></tr>
                    <tr><td><i class="fa-solid fa-flask-vial"></i> pH Value</td><td><?= htmlspecialchars($ph) ?></td></tr>
                    <tr><td><i class="fa-solid fa-tint"></i> Moisture</td><td><?= htmlspecialchars($moisture) ?>%</td></tr>
                    <tr><td><i class="fa-solid fa-mountain-sun"></i> Soil Type</td><td><?= htmlspecialchars($soil_type) ?></td></tr>
                    <tr><td><i class="fa-solid fa-temperature-three-quarters"></i> Temperature</td><td><?= htmlspecialchars($temperature) ?>°C</td></tr>
                    <tr><td><i class="fa-solid fa-droplet"></i> Humidity</td><td><?= htmlspecialchars($humidity) ?>%</td></tr>
                    <tr><td><i class="fa-solid fa-leaf"></i> Soil Quality Score</td><td><?= $soil_quality ?>%</td></tr>
                </table>
            </div>
            <div class="col">
                <div class="section-box">
                    <h3><i class="fa-solid fa-lightbulb"></i> Smart Recommendation</h3>
                    <p><?= $recommendation ?></p>
                </div>
                <div class="section-box">
                    <h3><i class="fa-solid fa-seedling"></i> Organic Fertilizers</h3>
                    <p><?= $organic_fertilizer ?></p>
                </div>
                <div class="section-box">
                    <h3><i class="fa-solid fa-flask"></i> Chemical Fertilizers</h3>
                    <p><?= $chemical_fertilizer ?></p>
                </div>
                <div class="section-box">
                    <h3><i class="fa-solid fa-wheat-awn"></i> Sample Suitable Crops</h3>
                    <p><?= implode(", ", $crop_suggestion) ?></p>
                </div>
            </div>
        </div>
        <div class="buttons">
            <a href="crop_advice.php?city=<?= urlencode($city) ?>"><i class="fa-solid fa-wheat-awn"></i> View Crop Advice</a>
            <a href="soil.php"><i class="fa-solid fa-rotate"></i> Recheck Soil</a>
            <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
        </div>
    <?php endif; ?>
</div>
<div class="footer">
    &copy; <?php echo date('Y'); ?> Smart Agriculture | Empowering Farmers for a Greener Tomorrow
</div>
<script>
function updateRecommendations() {
    const soilType = document.getElementById('soil_type').value;
    const ph = document.getElementById('ph').value;
    const moisture = document.getElementById('moisture').value;
    const city = '<?= htmlspecialchars($city) ?>';
    const hasPh = ph !== '' && !isNaN(ph);
    const hasMoisture = moisture !== '' && !isNaN(moisture);
    if (!soilType) {
        document.getElementById('recommendations').innerHTML = '';
        return;
    }
    fetch('get_crop_recommendation.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `soil_type=${encodeURIComponent(soilType)}&ph=${encodeURIComponent(ph)}&moisture=${encodeURIComponent(moisture)}&city=${encodeURIComponent(city)}`
    })
    .then(res => res.json())
    .then(data => {
        const recDiv = document.getElementById('recommendations');
        let html = '';
        if (hasPh && hasMoisture && data.exact_matches && data.exact_matches.length > 0) {
            html += `<div class='section-box'><strong>Best Match for Your Input:</strong></div>`;
            data.exact_matches.forEach(rec => {
                html += `<div class='section-box'>
                    <p><strong>Crops:</strong> ${rec.crops.join(', ')}</p>
                    <p><strong>Scientific Note:</strong> ${rec.note}</p>
                </div>`;
            });
        }
        if (hasPh && hasMoisture && data.near_matches && data.near_matches.length > 0) {
            html += `<div class='section-box'><strong>Other Near Matches:</strong></div>`;
            data.near_matches.forEach(rec => {
                html += `<div class='section-box'>
                    <p><strong>Crops:</strong> ${rec.crops.join(', ')}</p>
                    <p><strong>Scientific Note:</strong> ${rec.note}</p>
                </div>`;
            });
        }
        if ((!hasPh || !hasMoisture) && data.all_soil_type_crops && data.all_soil_type_crops.length > 0) {
            html += `<div class='section-box'><strong>All Crops for Selected Soil Type:</strong></div>`;
            data.all_soil_type_crops.forEach(rec => {
                html += `<div class='section-box'>
                    <p><strong>Crops:</strong> ${rec.crops.join(', ')}</p>
                    <p><strong>Scientific Note:</strong> ${rec.note}</p>
                </div>`;
            });
        }
        if (!html) {
            html = '<div class="section-box">No matches found for your input.</div>';
        }
        recDiv.innerHTML = html;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    ['soil_type', 'ph', 'moisture'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateRecommendations);
    });
});
</script>
</body>
</html>
