<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Smart Agriculture | Weather Dashboard</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #eef4f7;
      min-height: 100vh;
      overflow-y: auto;
    }
    #google_translate_element {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 999;
    }
    .main-container {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: space-between;
      padding: 20px;
      gap: 20px;
    }
    .weather-left {
      flex: 1;
      max-width: 300px;
      background: #14532d;
      color: white;
      padding: 20px;
      border-radius: 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .weather-left h2 {
      font-size: 20px;
      margin-bottom: 10px;
    }
    .weather-left img {
      width: 70px;
      margin: 10px 0;
    }
    .weather-left .stat {
      background: rgba(255,255,255,0.15);
      padding: 10px 15px;
      margin: 4px 0;
      border-radius: 10px;
      width: 100%;
      text-align: center;
      font-size: 14px;
    }
    .hourly-row {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 10px;
      gap: 10px;
    }
    .hour-card {
      background: rgba(255,255,255,0.1);
      padding: 8px;
      border-radius: 8px;
      width: 100px;
      text-align: center;
      font-size: 13px;
    }
    .forecast-right {
      flex: 3;
      min-width: 300px;
      padding: 20px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .forecast-right h1 {
      text-align: center;
      color: #0f172a;
      margin-bottom: 20px;
    }
    .forecast-cards {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }
    .card {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      padding: 20px;
      width: 160px;
      text-align: center;
    }
    .card img { width: 50px; }
    .card .label {
      font-size: 14px;
      color: #475569;
      margin-top: 5px;
    }
    .dashboard-btn {
      margin-top: 30px;
      background-color: #2e7d32;
      color: white;
      padding: 12px 22px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 16px;
    }
    .dashboard-btn:hover {
      background-color: #1b5e20;
    }
    @media (max-width: 768px) {
      .main-container {
        flex-direction: column;
      }
      .weather-left, .forecast-right {
        width: 100%;
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

<div class="main-container">
  <!-- Left: Current Weather & Hourly -->
  <div class="weather-left">
    <h2>Today's Weather</h2>
    <img id="icon" src="" alt="Weather Icon" />
    <div class="stat" id="weather">--</div>
    <div class="stat">Temp: <span id="temp">--</span> °C</div>
    <div class="stat">Feels Like: <span id="feels_like">--</span> °C</div>
    <div class="stat">Humidity: <span id="humidity">--</span>%</div>
    <div class="stat">Rainfall: <span id="precip">--</span> mm</div>
    <div class="stat">Rain Chance: <span id="chance">--</span>%</div>
    <div class="stat">Last Updated: <span id="updated">--</span></div>
    <h3 style="margin-top:15px; font-size: 16px;">Hourly Forecast</h3>
    <div class="hourly-row" id="hourly"></div>
  </div>

  <!-- Right: 5-Day Forecast -->
  <div class="forecast-right">
    <h1>5-Day Forecast</h1>
    <div class="forecast-cards" id="forecast"></div>

    <!-- Dashboard Button -->
    <a href="dashboard.php" class="dashboard-btn">🏠 Back to Dashboard</a>
  </div>
</div>

<script>
function loadWeather(lat, lon) {
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
    })
    .catch(err => {
      console.error("Weather fetch failed:", err);
      alert("Failed to load weather data. Check console.");
    });
}

navigator.geolocation.getCurrentPosition(
  pos => loadWeather(pos.coords.latitude, pos.coords.longitude),
  err => {
    alert("Allow location to get live weather data.");
    loadWeather(12.9716, 77.5946); // Fallback to Bangalore
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
