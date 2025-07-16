<?php  
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Smart Agriculture Advisor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            background-color: #f1f8e9;
            min-height: 100vh;
            overflow: hidden;
        }
        .sidebar {
            width: 250px;
            background-color: #33691e;
            color: white;
            padding: 20px 20px 30px 20px; /* Added bottom padding */
            display: flex;
            flex-direction: column;
        }
        .sidebar h1 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .sidebar .welcome {
            font-size: 1rem;
            margin-bottom: 20px;
            color: #dcedc8;
        }
        .nav-link {
            background-color: #558b2f;
            padding: 12px 15px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
            display: block;
            transition: background-color 0.3s;
        }
        .nav-link:hover {
            background-color: #689f38;
        }
        .logout-btn {
            margin-top: auto;
            margin-bottom: 10px; /* Slightly lifted from bottom */
            background-color: #d32f2f;
        }
        .logout-btn:hover {
            background-color: #b71c1c;
        }
        .main-content {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        .top-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }
        .weather-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .weather-card {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 20px;
            width: 250px;
            text-align: center;
        }
        .weather-card .icon {
            font-size: 2.5rem;
        }
        .weather-card .temp {
            font-size: 1.4rem;
            font-weight: bold;
            color: #33691e;
        }
        .weather-card .status {
            color: #616161;
            font-size: 0.95rem;
            margin-top: 5px;
        }
        .weather-card a {
            display: inline-block;
            margin-top: 10px;
            background-color: #43a047;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .weather-card a:hover {
            background-color: #2e7d32;
        }
        .chart-container {
            background: #ffffff;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            width: 250px;
            margin-top: 20px;
        }
        .top-prices {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 300px;
            margin-bottom: 20px;
        }
        .top-prices table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .top-prices th, .top-prices td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        .top-prices th {
            background-color: #dcedc8;
        }
        .crop-suggestions {
            background: #ffffff;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            font-size: 0.95rem;
            margin-top: 20px; /* Adds space from top crop box */
        }
        .crop-suggestions h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .crop-box {
            margin-bottom: 12px;
            padding: 8px;
            background-color: #f9fff0;
            border-left: 4px solid #558b2f;
            border-radius: 6px;
        }
        .crop-box h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
        }
        .crop-box p {
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,hi,kn,ta,te,ml,bn,gu,mr,pa,ur',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>
<div class="sidebar">
    <h1>🌿 Smart Agriculture</h1>
    <div class="welcome">Hello <?php echo htmlspecialchars($fullname); ?> 👋</div>
    <a href="profile_dashboard.php" class="nav-link">🌱 My Profile</a>
    <a href="weather_dashboard.php" class="nav-link">🌦️ Weather forecast</a>
    <a href="soil.php" class="nav-link">🩺 Soil Analysis</a>
    <a href="crop_advice.php" class="nav-link">💰 Crop Advice</a>
    <a href="irrigation_tips.php" class="nav-link">📈 Irrigation Tips</a>
    <a href="marketing_price.php" class="nav-link">📊 Market Prices</a>
    <a href="logout.php" class="nav-link logout-btn">🔓 Logout</a>
</div>
<div class="main-content">
    <h2 style="margin-top: 0; color: #2e7d32;">Welcome to Smart Agriculture Advisor Dashboard</h2>
    <div id="google_translate_element" style="position: absolute; top: 20px; right: 30px;"></div>
    <div class="top-section">
        <div class="top-prices">
            <h3>🔝 Top 3 Highest Crop Prices</h3>
            <table>
                <tr><th>Crop</th><th>Market</th><th>Modal Price (₹)</th></tr>
                <?php
                $apiKey = "579b464db66ec23bdd0000011ec418109033452d47392bb62daee529";
                $url = "https://api.data.gov.in/resource/9ef84268-d588-465a-a308-a864a43d0070?api-key=$apiKey&format=json&limit=1000";
                $response = @file_get_contents($url);
                $data = json_decode($response, true);
                if ($data && !empty($data['records'])) {
                    $latestDate = max(array_column($data['records'], 'arrival_date'));
                    $filtered = array_filter($data['records'], fn($i) => $i['arrival_date'] === $latestDate);
                    usort($filtered, fn($a, $b) => (int)$b['modal_price'] <=> (int)$a['modal_price']);
                    $top3 = array_slice($filtered, 0, 3);
                    foreach ($top3 as $item) {
                        echo "<tr><td>{$item['commodity']}</td><td>{$item['market']}</td><td><strong>{$item['modal_price']}</strong></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No crop data found.</td></tr>";
                }
                ?>
            </table>
            <div class="crop-suggestions" id="crop-suggestions">
                <h3>🌾 Best Crops to Sow Now in Your Region</h3>
                <?php
                $conn = mysqli_connect("localhost", "root", "", "smart_agri");
                $district = "Mandya";
                $month = date('F');
                $locationJson = @file_get_contents("https://ipapi.co/json/");
                if ($locationJson !== false) {
                    $location = json_decode($locationJson, true);
                    $district = $location['city'] ?? $district;
                }
                $sql = "SELECT * FROM district_crop_recommendation WHERE district LIKE '%$district%' AND month LIKE '%$month%'";
                $result = mysqli_query($conn, $sql);
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='crop-box'>";
                        echo "<h4>🌱 " . htmlspecialchars($row['crop1']) . ", " . htmlspecialchars($row['crop2']) . ", " . htmlspecialchars($row['crop3']) . "</h4>";
                        echo "<p>" . htmlspecialchars($row['summary']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No crop recommendation found for $district in $month.</p>";
                }
                ?>
            </div>
        </div>
        <div class="weather-section">
            <div class="weather-card" id="weather">
                <div class="icon">⏳</div>
                <div class="temp">Loading...</div>
                <div class="status">Fetching weather</div>
                <a href="weather_dashboard.php">View More</a>
            </div>
            <div class="chart-container">
                <h4 style="margin: 10px 0 5px; color:#2e7d32;">🌧️ Rainfall (mm)</h4>
                <canvas id="rainChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const weatherCard = document.getElementById("weather");
        const apiKey = "7b46741e515713880330945106c0d3d8";
        try {
            const locationRes = await fetch('https://ipapi.co/json');
            const location = await locationRes.json();
            const city = location.city || 'Bangalore';
            const weatherRes = await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`);
            const weather = await weatherRes.json();
            const temp = weather.main.temp + "°C";
            const description = weather.weather[0].description;
            const icon = weather.weather[0].main;
            let iconEmoji = "🌦️";
            if (icon.includes("Rain")) iconEmoji = "🌧️";
            else if (icon.includes("Clear")) iconEmoji = "☀️";
            else if (icon.includes("Cloud")) iconEmoji = "☁️";
            else if (icon.includes("Thunder")) iconEmoji = "⛈️";
            weatherCard.querySelector(".icon").textContent = iconEmoji;
            weatherCard.querySelector(".temp").textContent = temp;
            weatherCard.querySelector(".status").textContent = description.charAt(0).toUpperCase() + description.slice(1);
        } catch (err) {
            weatherCard.querySelector(".icon").textContent = "⚠️";
            weatherCard.querySelector(".temp").textContent = "N/A";
            weatherCard.querySelector(".status").textContent = "Weather unavailable";
            console.error("Weather load failed:", err);
        }
    });

    const ctx = document.getElementById('rainChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['2025', '2024', '2023', '2022', '2021'],
            datasets: [{
                label: 'Rainfall (mm)',
                data: [1040, 1025, 980, 890, 1125],
                backgroundColor: '#66bb6a'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Rainfall (mm)' }
                },
                x: {
                    title: { display: true, text: 'Year' }
                }
            }
        }
    });
</script>
</body>
</html>
