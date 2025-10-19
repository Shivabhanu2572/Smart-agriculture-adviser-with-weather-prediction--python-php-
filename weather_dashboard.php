<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Smart Agriculture | Weather Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      background: linear-gradient(120deg, #e8f5e9 0%, #f0f9f4 100%);
      min-height: 100vh;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }
    #google_translate_element {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 999;
    }
    .hero {
      background: linear-gradient(90deg, #388e3c 60%, #a5d6a7 100%);
      color: white;
      padding: 28px 0 14px 0;
      text-align: center;
      box-shadow: 0 2px 10px rgba(56, 142, 60, 0.08);
      position: relative;
    }
    .hero .icon {
      font-size: 40px;
      margin-bottom: 6px;
      display: block;
    }
    .hero h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: 1px;
    }
    .hero p {
      margin: 8px 0 0 0;
      font-size: 1rem;
      font-weight: 400;
      color: #e8f5e9;
    }
    .main-container {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      padding: 16px 8px 8px 8px;
      gap: 16px;
      max-width: 1000px;
      margin: 0 auto;
      width: 100%;
    }
    .weather-left {
      flex: 1;
      max-width: 270px;
      background: linear-gradient(120deg, #43a047 60%, #b2f2c9 100%);
      color: white;
      padding: 16px 10px 14px 10px;
      border-radius: 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 3px 12px rgba(56, 142, 60, 0.10);
      min-width: 200px;
    }
    .weather-left h2 {
      font-size: 1.1rem;
      margin-bottom: 6px;
      font-weight: 700;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .weather-left img {
      width: 60px;
      margin: 8px 0 4px 0;
      filter: drop-shadow(0 2px 8px rgba(0,0,0,0.10));
    }
    .weather-left .stat {
      background: rgba(255,255,255,0.13);
      padding: 8px 10px;
      margin: 3px 0;
      border-radius: 8px;
      width: 100%;
      text-align: center;
      font-size: 0.95rem;
      font-weight: 500;
      letter-spacing: 0.2px;
      box-shadow: 0 1px 4px rgba(56, 142, 60, 0.06);
    }
    .hourly-row {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 8px;
      gap: 6px;
    }
    .hour-card {
      background: linear-gradient(0deg, #b2f2c9 0%, #f5f5f5 80%);
      padding: 7px 4px 6px 4px;
      border-radius: 8px;
      width: 70px;
      text-align: center;
      font-size: 0.85rem;
      color: #256029;
      box-shadow: 0 1px 4px rgba(56, 142, 60, 0.06);
    }
    .forecast-right {
      flex: 3;
      min-width: 260px;
      padding: 16px 10px 14px 10px;
      background: #f5f5f5;
      border-radius: 14px;
      box-shadow: 0 3px 12px rgba(56, 142, 60, 0.10);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .forecast-right h1 {
      text-align: center;
      color: #388e3c;
      margin-bottom: 12px;
      font-size: 1.3rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .forecast-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-bottom: 10px;
    }
    .card {
      background: linear-gradient(135deg, #b2f2c9 60%, #f5f5f5 100%);
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(67,160,71,0.08);
      padding: 12px 6px 10px 6px;
      width: 120px;
      text-align: center;
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .card:hover {
      transform: translateY(-2px) scale(1.02);
      box-shadow: 0 4px 12px rgba(67,160,71,0.13);
    }
    .card img { width: 38px; margin-bottom: 4px; }
    .card .label {
      font-size: 0.95rem;
      color: #388e3c;
      margin-top: 4px;
      font-weight: 500;
    }
    .dashboard-btn {
      margin-top: 18px;
      background: linear-gradient(90deg, #43a047 60%, #388e3c 100%);
      color: white;
      padding: 10px 22px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 700;
      box-shadow: 0 1px 4px rgba(67, 160, 71, 0.10);
      transition: background 0.2s, box-shadow 0.2s;
      display: inline-block;
    }
    .dashboard-btn:hover {
      background: linear-gradient(90deg, #256029 60%, #43a047 100%);
      box-shadow: 0 2px 8px rgba(56, 142, 60, 0.18);
    }
    .tips-card {
      background: linear-gradient(120deg, #b2f2c9 60%, #f5f5f5 100%);
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(56, 142, 60, 0.10);
      padding: 12px 8px 10px 8px;
      width: 100%;
      margin-top: 14px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      min-height: 80px;
      animation: fadeIn 0.7s;
    }
    .tips-title {
      font-size: 1rem;
      font-weight: 700;
      color: #256029;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .tips-list {
      font-size: 0.95rem;
      color: #256029;
      margin-left: 0;
      padding-left: 14px;
    }
    .alert-card {
      background: linear-gradient(120deg, #ffe082 60%, #fffde7 100%);
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(255, 193, 7, 0.10);
      padding: 12px 8px 10px 8px;
      width: 100%;
      margin-top: 12px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      min-height: 60px;
      animation: fadeIn 0.7s;
    }
    .alert-title {
      font-size: 1rem;
      font-weight: 700;
      color: #b26a00;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .alert-message {
      font-size: 0.95rem;
      color: #b26a00;
      margin-left: 0;
      padding-left: 14px;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .footer {
      margin-top: auto;
      background: #388e3c;
      color: #e8f5e9;
      text-align: center;
      padding: 10px 0 6px 0;
      font-size: 0.95rem;
      letter-spacing: 0.5px;
      box-shadow: 0 -1px 6px rgba(56, 142, 60, 0.08);
    }
    @media (max-width: 900px) {
      .main-container {
        flex-direction: column;
        gap: 10px;
        padding: 10px 2vw 10px 2vw;
      }
      .weather-left, .forecast-right {
        width: 100%;
        min-width: unset;
        max-width: unset;
      }
      .forecast-cards {
        gap: 8px;
      }
    }
    @media (max-width: 600px) {
      .hero h1 {
        font-size: 1.1rem;
      }
      .forecast-right h1 {
        font-size: 1rem;
      }
      .main-container {
        padding: 4px 1vw 4px 1vw;
      }
      .card {
        width: 98vw;
        min-width: 0;
        max-width: 100%;
        padding: 8px 2px 6px 2px;
      }
    }
    .goog-te-banner-frame.skiptranslate, .goog-logo-link, .goog-te-gadget img {
      display: none !important;
    }
    body { top: 0 !important; }
  </style>
</head>
<body>
<div id="google_translate_element"></div>

<div class="hero">
  <span class="icon"><i class="fa-solid fa-cloud-sun-rain"></i></span>
  <h1>Weather Dashboard</h1>
  <p>Stay ahead of the weather. Get real-time and forecasted weather insights for your farm!</p>
</div>

<div class="main-container">
  <!-- Left: Current Weather & Hourly -->
  <div class="weather-left">
    <h2><i class="fa-solid fa-sun"></i> Today's Weather</h2>
    <img id="icon" src="" alt="Weather Icon" />
    <div class="stat" id="weather">--</div>
    <div class="stat">Temp: <span id="temp">--</span> °C</div>
    <div class="stat">Feels Like: <span id="feels_like">--</span> °C</div>
    <div class="stat">Humidity: <span id="humidity">--</span>%</div>
    <div class="stat">Rainfall: <span id="precip">--</span> mm</div>
    <div class="stat">Rain Chance: <span id="chance">--</span>%</div>
    <div class="stat">Last Updated: <span id="updated">--</span></div>
    <h3 style="margin-top:15px; font-size: 1.1rem; font-weight: 700; letter-spacing: 0.2px;"><i class="fa-solid fa-clock"></i> Hourly Forecast</h3>
    <div class="hourly-row" id="hourly"></div>
  </div>

  <!-- Right: 5-Day Forecast -->
  <div class="forecast-right">
    <h1><i class="fa-solid fa-calendar-days"></i> 5-Day Forecast</h1>
    <div class="forecast-cards" id="forecast"></div>

    <div class="tips-card" id="tips-card">
      <div class="tips-title"><i class="fa-solid fa-lightbulb"></i> Smart Farming Tips</div>
      <ul class="tips-list" id="tips-list">
        <li>Loading tips...</li>
      </ul>
    </div>
    <div class="alert-card" id="alert-card">
      <div class="alert-title"><i class="fa-solid fa-triangle-exclamation"></i> Weather Alerts</div>
      <div class="alert-message" id="alert-message">Loading alerts...</div>
    </div>

    <!-- Dashboard Button -->
    <a href="dashboard.php" class="dashboard-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
  </div>
</div>

<div class="footer">
  &copy; <?php echo date('Y'); ?> Smart Agriculture | Weather Insights for Smarter Farming
</div>

<script>
let lastLat = null;
let lastLon = null;
function loadWeather(lat, lon) {
  lastLat = lat;
  lastLon = lon;
  fetch(`weather.php?lat=${lat}&lon=${lon}&ts=${Date.now()}`)
    .then(res => res.json())
    .then(data => {
      document.getElementById("temp").innerText = data.current.temp;
      document.getElementById("feels_like").innerText = data.current.feels_like;
      document.getElementById("weather").innerText = data.current.weather;
      document.getElementById("humidity").innerText = data.current.humidity;
      document.getElementById("precip").innerText = data.current.precip;
      document.getElementById("chance").innerText = data.current.chance;
      document.getElementById("updated").innerText = data.current.updated;
      document.getElementById("icon").src = `https://openweathermap.org/img/wn/${data.current.icon}@2x.png`;

      const forecast = document.getElementById("forecast");
      forecast.innerHTML = "";
      data.forecast.forEach(day => {
        const date = new Date(day.date);
        forecast.innerHTML += `
          <div class="card">
            <h4>${date.toDateString()}</h4>
            <img src="https://openweathermap.org/img/wn/${day.icon}@2x.png" />
            <div class="label">Temp: ${day.temp} °C</div>
            <div class="label">${day.weather}</div>
          </div>`;
      });

      const hourly = document.getElementById("hourly");
      hourly.innerHTML = "";
      data.hourly.forEach(h => {
        hourly.innerHTML += `
          <div class="hour-card">
            <strong>${h.time}</strong><br/>
            🌡 ${h.temp} °C<br/>
            🌧 ${h.rain}%<br/>
            🌬 ${h.wind} m/s
          </div>`;
      });

      // Smart Farming Tips
      const tipsList = document.getElementById("tips-list");
      const tips = [];
      const temp = data.current.temp;
      const rain = parseFloat(data.current.precip);
      const chance = parseFloat(data.current.chance);
      const humidity = parseFloat(data.current.humidity);
      const weather = data.current.weather.toLowerCase();

      if (rain > 5 || chance > 60 || weather.includes('rain')) {
        tips.push("Rain expected: Reduce irrigation and check drainage.");
      }
      if (temp > 35) {
        tips.push("High temperature: Mulch soil and irrigate early morning or late evening.");
      }
      if (humidity > 80) {
        tips.push("High humidity: Monitor for fungal diseases and ventilate greenhouses.");
      }
      if (temp < 15) {
        tips.push("Low temperature: Protect young plants from cold and consider row covers.");
      }
      if (weather.includes('clear')) {
        tips.push("Clear weather: Good time for harvesting and field work.");
      }
      if (tips.length === 0) {
        tips.push("Weather is stable. Continue regular farm activities and monitor updates.");
      }
      tipsList.innerHTML = tips.map(t => `<li>${t}</li>`).join('');

      // Weather Alerts
      const alertMessage = document.getElementById("alert-message");
      if (data.alert && data.alert !== "No weather alerts from free API") {
        alertMessage.innerHTML = data.alert;
      } else {
        alertMessage.innerHTML = "No severe weather alerts for your area. All clear!";
      }
    })
    .catch(err => {
      console.error("Weather fetch failed:", err);
      alert("Failed to load weather data. Check console.");
    });
}

navigator.geolocation.getCurrentPosition(
  pos => {
    loadWeather(pos.coords.latitude, pos.coords.longitude);
    // Auto-refresh every 5 minutes
    setInterval(() => {
      loadWeather(pos.coords.latitude, pos.coords.longitude);
    }, 300000);
  },
  err => {
    alert("Allow location to get live weather data.");
    loadWeather(12.9716, 77.5946); // Fallback to Bangalore
    setInterval(() => {
      loadWeather(12.9716, 77.5946);
    }, 300000);
  }
);
</script>

<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,kn,hi,ta,te',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
